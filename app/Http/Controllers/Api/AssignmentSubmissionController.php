<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{AssignmentSubmission, Course, Sequence};
use App\Services\AccessService;
use Illuminate\Http\Request;

class AssignmentSubmissionController extends Controller
{
    public function __construct(
        private readonly AccessService $access,
    ) {}

    // ========================================================
    // POST /api/sequences/{sequence}/submission — 🔒 level 2+
    // (upsert : le re-soumission remplace la version précédente)
    // ========================================================
    public function store(Request $request, Sequence $sequence)
    {
        $course = $sequence->chapter->course;

        // 🔒 GATING BACKEND
        if (! $this->access->can($request->user(), $course, 'assignment_review')) {
            return response()->json([
                'message'        => 'La soumission de devoirs avec correction personnalisée nécessite la formule Standard.',
                'required_level' => 2,
            ], 403);
        }

        $data = $request->validate([
            'file'    => ['required', 'file', 'mimes:pdf,doc,docx,zip', 'max:10240'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        // ✅ Chemin RELATIF au disque (règle établie — jamais d'URL absolue)
        $filePath = $request->file('file')->store(
            'soumissions/' . $course->id . '/' . $request->user()->id,
            'public'
        );

        // Upsert : une seule soumission par apprenant/séquence, mise à jour possible
        $submission = AssignmentSubmission::updateOrCreate(
            ['user_id' => $request->user()->id, 'sequence_id' => $sequence->id],
            [
                'course_id' => $course->id,
                'file_path' => $filePath,
                'comment'   => $data['comment'] ?? null,
                'status'    => 'soumis',
                'grade'     => null,                    // re-soumission → re-correction
                'instructor_feedback' => null,
            ]
        );

        return response()->json([
            'message' => 'Devoir soumis avec succès. Ton formateur le corrigera rapidement.',
            'data'    => $this->format($submission),
        ], 201);
    }

    // GET /api/sequences/{sequence}/submission — ma soumission pour cette séquence
    public function show(Request $request, Sequence $sequence)
    {
        $submission = AssignmentSubmission::where('user_id', $request->user()->id)
            ->where('sequence_id', $sequence->id)
            ->first();

        return response()->json([
            'data' => $submission ? $this->format($submission) : null,
        ]);
    }

    // GET /api/courses/{course}/my-submissions — toutes mes soumissions d'un cours
    public function indexForCourse(Request $request, Course $course)
    {
        $submissions = AssignmentSubmission::with('sequence')
            ->where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $submissions->map(fn ($s) => $this->format($s)),
        ]);
    }

    private function format(AssignmentSubmission $s): array
    {
        return [
            'id'        => (string) $s->id,
            'sequenceId'=> (string) $s->sequence_id,
            'sequence'  => $s->sequence?->title,
            'status'    => $s->status,
            'grade'     => $s->grade,
            'feedback'  => $s->instructor_feedback,
            'fileUrl'   => $s->file_path ? '/storage/' . $s->file_path : null,
            'comment'   => $s->comment,
            'submittedAt' => $s->created_at->format('d/m/Y H:i'),
        ];
    }

        // ========================================================
    // GET /api/formateur/submissions — toutes les soumissions
    // des cours du formateur connecté
    // ========================================================
    public function indexForInstructor(Request $request)
    {
        $user = $request->user();

        $submissions = AssignmentSubmission::with([
                'user:id,name',
                'sequence:id,title',
                'course:id,slug,title',
            ])
            ->whereHas('course', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $submissions->map(fn ($s) => $this->formatForInstructor($s)),
        ]);
    }

    // ========================================================
    // PATCH /api/formateur/submissions/{submission} — corriger
    // ========================================================
    public function grade(Request $request, AssignmentSubmission $submission)
    {
        $user = $request->user();

        if (
            (int) $submission->course->instructor_id !== (int) $user->id
            && ! $user->hasRole('admin')
        ) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'grade'               => ['required', 'integer', 'min:0', 'max:20'],
            'instructor_feedback' => ['nullable', 'string', 'max:5000'],
            'status'              => ['required', 'in:corrige,retourne'],
        ]);

        $submission->update($data);
        // notification via alertes
        // ✅ NOTIFICATION : devoir corrigé → l'apprenant
        \App\Services\NotificationService::send(
            'devoir_corrige',
            'Ton devoir a été corrigé : ' . $submission->grade . '/20',
            'Séquence « ' . $submission->sequence->title . ' » — Feedback : ' . ($submission->instructor_feedback ?? 'Consulte ta correction.'),
            $submission->user,                       // destinataire = l'apprenant
            $submission->course->slug,
        );

        return response()->json([
            'message' => 'Correction enregistrée.',
            'data'    => $this->formatForInstructor($submission->fresh()),
        ]);
    }

    private function formatForInstructor(AssignmentSubmission $s): array
    {
        return [
            'id'           => (string) $s->id,
            'studentName'  => $s->user?->name ?? 'Apprenant',
            'sequenceTitle'=> $s->sequence?->title ?? '',
            'courseTitle'  => $s->course?->title ?? '',
            'status'       => $s->status,           // soumis|corrige|retourne
            'grade'        => $s->grade,
            'feedback'     => $s->instructor_feedback,
            'fileUrl'      => $s->file_path ? '/storage/' . $s->file_path : null,
            'comment'      => $s->comment,
            'submittedAt'  => $s->created_at->format('d/m/Y H:i'),
        ];
    }
}