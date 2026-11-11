<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Course, Enrollment, LiveSession, Payment};
use App\Services\AccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AccessService $access,
    ) {}

    // ============================================================
    // GET /api/my-dashboard — 🔒 auth:sanctum
    // Tout le tableau de bord personnalisé en un seul appel.
    // ============================================================
    public function index(Request $request)
    {
        $user = $request->user();

        $enrollments = Enrollment::with('course')
            ->where('user_id', $user->id)
            ->orderByDesc('last_accessed_at')
            ->get();

        // --------------------------------------------------------
        // 1. 📊 VOS COURS (+ recherche du point de reprise)
        // --------------------------------------------------------

        $courses = [];
        $resume  = null;
        $completedCount = 0;

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (! $course) continue;

            $progress = $enrollment->progressPercent();
            $level    = $this->access->levelFor($user, $course);

            if ($progress >= 100) $completedCount++;

            $courses[] = [
                'slug'            => $course->slug,
                'title'           => $course->title,
                'image'           => $this->mediaUrl($course->image),
                'category'        => $course->category,
                'progressPercent' => $progress,
                'accessLevel'     => $level,
                'lastAccessedAt'  => $enrollment->last_accessed_at
                    ? $enrollment->last_accessed_at->diffForHumans()
                    : 'Jamais',
            ];

            // ▶ « Reprendre » = le cours le plus récemment accédé
            //   qui n'est PAS terminé, et sa 1ère séquence non complétée.
            if ($progress < 100 && ! $resume) {
                $next = $this->findNextSequence($enrollment);

                if ($next) {
                    $resume = [
                        'courseSlug'      => $course->slug,
                        'courseTitle'     => $course->title,
                        'courseImage'     => $this->mediaUrl($course->image),
                        'sequenceId'      => (string) $next->id,
                        'sequenceTitle'   => $next->title,
                        'chapterTitle'    => $next->chapter_title,
                        'progressPercent' => $progress,
                    ];
                }
            }
        }

        // --------------------------------------------------------
        // 2. 🔴 SÉANCES EN DIRECT (sur les cours de l'utilisateur)
        //    LiveSession.course_id stocke slug OU id → on teste les deux.
        // --------------------------------------------------------

        $slugsAndIds = $enrollments->flatMap(fn ($e) => $e->course
            ? [$e->course->slug, (string) $e->course->id]
            : []
        )->unique()->values();

        $liveNow = LiveSession::where('is_live', true)
            ->whereIn('course_id', $slugsAndIds)
            ->get()
            ->map(function ($session) use ($enrollments) {
                // Retrouve le titre du cours correspondant
                $course = $enrollments->first(fn ($e) =>
                    $e->course && (
                        $e->course->slug === $session->course_id ||
                        (string) $e->course->id === $session->course_id
                    )
                )?->course;

                return [
                    'courseSlug' => $course?->slug ?? $session->course_id,
                    'courseTitle'=> $course?->title ?? $session->course_id,
                    'roomName'   => $session->room_name,
                ];
            })
            ->values();

        // --------------------------------------------------------
        // 3. 🏆 À RENDRE : devoirs/TP non soumis (max 5)
        // --------------------------------------------------------

        $courseIds = $enrollments->pluck('course_id')->filter()->values();

        $deadlines = collect();

        if ($courseIds->isNotEmpty()) {
            $deadlines = DB::table('sequences')
                ->join('chapters', 'chapters.id', '=', 'sequences.chapter_id')
                ->leftJoin('assignment_submissions', function ($join) use ($user) {
                    $join->on('assignment_submissions.sequence_id', '=', 'sequences.id')
                         ->where('assignment_submissions.user_id', $user->id);
                })
                ->whereIn('chapters.course_id', $courseIds)
                // ->whereIn('sequences.type', ['devoir', 'tp'])
                ->whereIn('sequences.type', ['devoir', 'tp', 'td', 'cas_pratique'])
                ->whereNull('assignment_submissions.id')   // pas encore soumis
                ->orderBy('chapters.course_id')
                ->orderBy('chapters.position')
                ->limit(5)
                ->get([
                    'sequences.id',
                    'sequences.title',
                    'sequences.type',
                    'chapters.course_id',
                ])
                ->map(function ($row) use ($enrollments) {
                    $course = $enrollments->first(fn ($e) =>
                        $e->course && (int) $e->course->id === (int) $row->course_id
                    )?->course;

                    return [
                        'courseSlug' => $course?->slug ?? '',
                        'courseTitle'=> $course?->title ?? '',
                        'sequenceId' => (string) $row->id,
                        'title'      => $row->title,
                        'kind'       => $row->type,
                    ];
                })
                ->values();
        }

        return response()->json([
            'data' => [
                'resume'    => $resume,
                'courses'   => $courses,
                'liveNow'   => $liveNow,
                'deadlines' => $deadlines,
                'stats'     => [
                    'coursesCount'   => count($courses),
                    'completedCount' => $completedCount,
                ],
            ],
        ]);
    }

    /** 1ère séquence non complétée du cours (par position). */
    private function findNextSequence(Enrollment $enrollment)
    {
        return DB::table('sequences')
            ->join('chapters', 'chapters.id', '=', 'sequences.chapter_id')
            ->leftJoin('sequence_progress', function ($join) use ($enrollment) {
                $join->on('sequence_progress.sequence_id', '=', 'sequences.id')
                     ->where('sequence_progress.enrollment_id', $enrollment->id);
            })
            ->where('chapters.course_id', $enrollment->course_id)
            ->whereNull('sequence_progress.id')
            ->orderBy('chapters.position')
            ->orderBy('sequences.position')
            ->select(
                'sequences.id',
                'sequences.title',
                'chapters.title as chapter_title'
            )
            ->first();
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) return null;
        if (str_starts_with($path, 'http') || str_starts_with($path, '/storage/')) return $path;
        return '/storage/' . $path;
    }
}