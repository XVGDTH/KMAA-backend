<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Course, Enrollment};
use App\Models\Payment;
use App\Models\PricingPlan;
use App\Models\AssignmentSubmission;
use App\Services\AccessService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function __construct(
        private readonly AccessService $access,
    ) {}

    // ============================================================
    // POST /api/enrollments — inscrire l'apprenant à un cours
    // ============================================================
    public function store(Request $request)
    {
        $data = $request->validate(['course_slug' => 'required|string|exists:courses,slug']);
        $course = Course::where('slug', $data['course_slug'])->firstOrFail();

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $request->user()->id, 'course_id' => $course->id],
            ['enrolled_at' => now()]
        );

        return response()->json(['data' => $this->format($enrollment)], 201);
    }

    // ============================================================
    // GET /api/me/enrollments — mes inscriptions (+ niveau d'accès)
    // ============================================================
    public function myEnrollments(Request $request)
    {
        $user   = $request->user();
        $access = $this->access;

        $enrollments = Enrollment::with('course')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'data' => $enrollments->map(function ($e) use ($access, $user) {
                $course = $e->course;

                return [
                    ...$this->format($e),

                    'access_level' => $course
                        ? $access->levelFor($user, $course)
                        : 0,
                ];
            }),
        ]);
    }

    // ============================================================
    // GET /api/courses/{slugOrId}/access — 🔒 Niveau d'accès
    // + capacités de l'utilisateur connecté pour ce cours.
    // Résout le cours par SLUG ou par ID.
    // ============================================================
    public function access(Request $request, string $slugOrId)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        $course = Course::where('slug', $slugOrId)->first()
            ?? Course::find($slugOrId);

        if (! $course) {
            return response()->json(['message' => 'Cours introuvable.'], 404);
        }

        $level = $this->access->levelFor($user, $course);

        $can = [];
        foreach (AccessService::FEATURES as $feature => $required) {
            $can[$feature] = $level >= $required;
        }

        return response()->json([
            'data' => [
                'level' => $level,
                'can'   => $can,
            ],
        ]);
    }

    // ============================================================
    // GET /api/courses/{slugOrId}/my-plan — 🔒 Ma formule
    // pour ce cours + toutes les formules (comparatif)
    // ============================================================
    public function myPlan(Request $request, string $slugOrId)
    {
        $user = $request->user();

        $course = Course::where('slug', $slugOrId)->first()
            ?? Course::find($slugOrId);

        if (! $course) {
            return response()->json(['message' => 'Cours introuvable.'], 404);
        }

        $level = $this->access->levelFor($user, $course);

        // La formule actuelle = le plan level MAX parmi les paiements validés
        $plan = Payment::with('pricingPlan')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'paye')
            ->get()
            ->sortByDesc(fn ($p) => $p->pricingPlan?->level ?? 1)
            ->first()?->pricingPlan;

        // Toutes les formules (pour afficher les upgrades possibles)
        $allPlans = PricingPlan::orderBy('level')->get()->map(fn ($p) => [
            'id'        => (string) $p->id,
            'name'      => $p->name,
            'level'     => $p->level,
            'price'     => (int) $p->price,
            'badge'     => $p->badge,
            'features'  => $p->features ?? [],
            'featured'  => (bool) $p->featured,
            'isCurrent' => $p->level === $level,
        ]);

        return response()->json([
            'data' => [
                'level'    => $level,
                'planName' => $plan?->name ?? 'Aucune formule',
                'allPlans' => $allPlans->values(),
            ],
        ]);
    }

    // ============================================================
    // GET /api/courses/{slugOrId}/my-evaluations — 🔒 auth
    // Agrège : devoirs soumis (notes/feedback) + quiz réussis.
    // ============================================================
    public function myEvaluations(Request $request, string $slugOrId)
    {
        $user = $request->user();

        $course = Course::where('slug', $slugOrId)->first()
            ?? Course::find($slugOrId);

        if (! $course) {
            return response()->json(['message' => 'Cours introuvable.'], 404);
        }

        // --------------------------------------------------------
        // 1. SOUMISSIONS DE DEVOIRS (notes + feedback du formateur)
        // --------------------------------------------------------

        $submissions = AssignmentSubmission::with('sequence:id,title,type')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($s) => [
                'type'        => 'devoir',
                'id'          => (string) $s->id,
                'sequenceId'  => (string) $s->sequence_id,
                'title'       => $s->sequence?->title ?? 'Devoir',
                'kind'        => $s->sequence?->type ?? 'devoir',
                'status'      => $s->status,
                'grade'       => $s->grade,
                'feedback'    => $s->instructor_feedback,
                'submittedAt' => $s->created_at->format('d/m/Y H:i'),
            ]);

        $average = $submissions->whereNotNull('grade')->avg('grade');

        // --------------------------------------------------------
        // 2. QUIZ RÉUSSIS
        //    sequence_progress n'a pas de user_id : on passe par
        //    enrollments (qui porte user_id ET course_id).
        //    Isolé dans try/catch → les devoirs s'affichent toujours.
        // --------------------------------------------------------

        $quizDone = collect();

        try {
            $quizDone = \DB::table('sequence_progress')
                ->join('enrollments', 'enrollments.id', '=', 'sequence_progress.enrollment_id')
                ->join('sequences', 'sequences.id', '=', 'sequence_progress.sequence_id')
                ->where('enrollments.user_id', $user->id)
                ->where('enrollments.course_id', $course->id)
                ->where('sequences.type', 'quiz')
                ->orderByDesc('sequence_progress.completed_at')
                ->get([
                    'sequences.id as sequence_id',
                    'sequences.title',
                    'sequence_progress.completed',
                    'sequence_progress.completed_at',
                ])
                ->map(function ($q) {
                    return [
                        'type'        => 'quiz',
                        'id'          => 'quiz-' . $q->sequence_id,
                        'sequenceId'  => (string) $q->sequence_id,
                        'title'       => $q->title,
                        'status'      => $q->completed ? 'reussi' : 'en_cours',
                        'grade'       => null,
                        'feedback'    => null,
                        'submittedAt' => $q->completed_at
                            ? \Carbon\Carbon::parse($q->completed_at)->format('d/m/Y H:i')
                            : '',
                    ];
                });
        } catch (\Throwable $e) {
            \Log::warning('myEvaluations — partie quiz ignorée : ' . $e->getMessage());
        }

        return response()->json([
            'data' => [
                'evaluations' => $submissions->concat($quizDone)->values(),
                'average'     => $average,
            ],
        ]);
    }

    // ============================================================
    // PRIVÉ
    // ============================================================
    private function format(Enrollment $e): array
    {
        return [
            'courseId'          => $e->course->slug,
            'progressPercent'   => $e->progressPercent(),
            'enrolledAt'        => $e->enrolled_at->format('d M Y'),
            'lastAccessedAt'    => $e->last_accessed_at?->diffForHumans() ?? 'Jamais',
            'certificateIssued' => $e->certificate_issued,
        ];
    }
}