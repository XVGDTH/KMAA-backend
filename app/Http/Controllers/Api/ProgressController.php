<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Course, Enrollment, Sequence, SequenceProgress, Feedback};
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    private const QUIZ_PASS_THRESHOLD = 85;

    // ============================================================
    // POST /api/sequences/{sequence}/complete
    // ============================================================
    public function complete(Request $request, int $sequenceId)
    {
            // ✅ On charge la séquence avec TOUTES ses relations possibles
    $sequence = Sequence::with(['chapter', 'module', 'course'])->findOrFail($sequenceId);

        // ✅ On cherche le course_id de toutes les manières possibles
    $courseId = $sequence->chapter?->course_id 
             ?? $sequence->module?->course_id 
             ?? $sequence->course_id;

    // Si vraiment on ne trouve pas, on renvoie une erreur claire au lieu de planter
    if (!$courseId) {
        return response()->json([
            'message' => 'Impossible de lier cette séquence à un cours. Vérifiez la structure du cours.'
        ], 422);
    }
        

        // ✅ Créer l'enrollment s'il n'existe pas (cas du formateur en test)
        // $enrollment = Enrollment::firstOrCreate(
        //     [
        //         'user_id'   => $request->user()->id,
        //         'course_id' => $sequence->chapter?->course_id ?? $sequence->course_id,
        //     ],
        //     [
        //         'enrolled_at' => now(),
        //     ]
        // );

            // ✅ On crée l'enrollment avec le bon course_id
    $enrollment = Enrollment::firstOrCreate(
        [
            'user_id'   => $request->user()->id,
            'course_id' => $courseId,
        ],
        [
            'enrolled_at' => now(),
        ]
    );



        // Quiz : scoring côté serveur
        if ($sequence->type === 'quiz') {
            $questions = is_string($sequence->quiz_questions)
                ? json_decode($sequence->quiz_questions, true)
                : $sequence->quiz_questions;

            if (empty($questions)) {
                return response()->json(['message' => 'Ce quiz n\'a pas encore de questions.'], 422);
            }

            $answers = $request->validate([
                'answers'   => ['required', 'array'],
                'answers.*' => ['nullable'],
            ])['answers'];

            $correctCount = 0;
            foreach ($questions as $i => $question) {
                $given = $answers[$i] ?? null;
                if (($question['type'] ?? 'qcm') === 'texte') {
                    $expected = strtolower(trim($question['expectedAnswer'] ?? ''));
                    if ($expected !== '' && strtolower(trim((string) $given)) === $expected) {
                        $correctCount++;
                    }
                } else {
                    if ((string) ($question['correctIndex'] ?? '') === (string) $given) {
                        $correctCount++;
                    }
                }
            }

            $scorePercent = (int) round($correctCount / count($questions) * 100);
            $passed = $scorePercent >= self::QUIZ_PASS_THRESHOLD;

            SequenceProgress::updateOrCreate(
                ['enrollment_id' => $enrollment->id, 'sequence_id' => $sequence->id],
                [
                    'score_percent' => $scorePercent,
                    'passed'        => $passed,
                    'completed'     => $passed,
                    'completed_at'  => $passed ? now() : null,
                ]
            );

            $this->checkCourseCompletion($enrollment);

            return response()->json([
                'data' => [
                    'scorePercent'    => $scorePercent,
                    'passed'          => $passed,
                    'threshold'       => self::QUIZ_PASS_THRESHOLD,
                    'progressPercent' => $enrollment->progressPercent(),
                    'courseCompleted' => $this->isCourseCompleted($enrollment),
                ],
            ]);
        }

        // Séquence simple
        SequenceProgress::updateOrCreate(
            ['enrollment_id' => $enrollment->id, 'sequence_id' => $sequence->id],
            ['completed' => true, 'completed_at' => now()]
        );

        $enrollment->update(['last_accessed_at' => now()]);
        $this->checkCourseCompletion($enrollment);

        return response()->json([
            'data' => [
                'progressPercent' => $enrollment->progressPercent(),
                'courseCompleted' => $this->isCourseCompleted($enrollment),
            ],
        ]);
    }

    // ============================================================
    // POST /api/sequences/{sequence}/feedback
    // ============================================================
public function feedback(Request $request, int $sequenceId)
{
    $data = $request->validate([
        'comment' => ['nullable', 'string', 'max:1000'],
        'rating'  => ['nullable', 'integer', 'min:1', 'max:5'],
    ]);

    // On charge les relations nécessaires pour trouver le course_id
    $sequence = Sequence::with(['chapter', 'module'])->find($sequenceId);
    
    if (! $sequence) {
        return response()->json(['message' => 'Séquence introuvable.'], 404);
    }

    // ✅ On cherche le course_id de toutes les manières possibles
    $courseId = $sequence->chapter?->course_id 
             ?? $sequence->module?->course_id 
             ?? $sequence->course_id;

    // Si vraiment on ne trouve pas, on renvoie une erreur claire au lieu de planter
    if (!$courseId) {
        return response()->json(['message' => 'Impossible de lier cet avis à un cours (ID manquant).'], 422);
    }

    Feedback::updateOrCreate(
        [
            'user_id'     => $request->user()->id,
            'sequence_id' => $sequenceId,
        ],
        [
            'course_id' => $courseId,
            'comment'   => $data['comment'] ?? null,
            'rating'    => $data['rating'] ?? null,
        ]
    );

    return response()->json(['message' => 'Avis enregistré.']);
}

    // ============================================================
    // GET /api/courses/{slugOrId}/enrollment-progress
    // ============================================================
    public function show(Request $request, string $slugOrId)
    {
        $course = Course::where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->first();

        if (! $course) {
            return response()->json(['message' => 'Cours introuvable.'], 404);
        }

        $enrollment = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        return response()->json([
            'data' => [
                'progressPercent' => $enrollment ? $enrollment->progressPercent() : 0,
            ],
        ]);
    }

    // ============================================================
    // HELPERS
    // ============================================================
    // private function isCourseCompleted(Enrollment $enrollment): bool
    // {
    //     $total = Sequence::whereHas('chapter', fn($q) => $q->where('course_id', $enrollment->course_id))->count();
    //     if ($total === 0) return false;

    //     $done = SequenceProgress::where('enrollment_id', $enrollment->id)
    //         ->where('completed', true)
    //         ->whereIn('sequence_id', Sequence::whereHas('chapter', fn($q) => $q->where('course_id', $enrollment->course_id))->pluck('id'))
    //         ->count();

    //     return $done >= $total;
    // }

    private function isCourseCompleted(Enrollment $enrollment): bool
{
    $courseId = $enrollment->course_id;

    // 1. On récupère TOUTES les séquences du cours
    $sequences = Sequence::whereHas('chapter', fn($q) => $q->where('course_id', $courseId))
        ->orWhereHas('module', fn($q) => $q->where('course_id', $courseId))
        ->orWhere('course_id', $courseId)
        ->get();

    if ($sequences->isEmpty()) return false;

    // 2. On récupère les progressions de cet enrollment
    $progressions = SequenceProgress::where('enrollment_id', $enrollment->id)
        ->get()
        ->keyBy('sequence_id');

    // 3. On récupère les soumissions de devoirs de l'apprenant
    $submissions = \App\Models\AssignmentSubmission::where('user_id', $enrollment->user_id)
        ->where('course_id', $courseId)
        ->get()
        ->keyBy('sequence_id');

    foreach ($sequences as $seq) {
        $prog = $progressions->get($seq->id);

        // ---- VIDÉO / DOCUMENT ----
        if (in_array($seq->type, ['video', 'document'])) {
            if (!$prog || !$prog->completed) return false;
        }

        // ---- QUIZ ----
        if ($seq->type === 'quiz') {
            if (!$prog || !$prog->completed || !$prog->passed) return false;
        }

        // ---- DEVOIR / TD / TP / CAS PRATIQUE ----
        if (in_array($seq->type, ['devoir', 'td', 'tp', 'cas_pratique'])) {
            // Il faut que le formateur ait corrigé la soumission
            if (!$prog || !$prog->completed) return false;
            $sub = $submissions->get($seq->id);
            if (!$sub || $sub->status !== 'corrige') return false;
        }
    }

    return true;
}

    // private function checkCourseCompletion(Enrollment $enrollment): void
    // {
    //     if ($this->isCourseCompleted($enrollment) && ! $enrollment->completed_at) {
    //         $enrollment->update(['completed_at' => now()]);
    //         $enrollment->refresh(); // On rafraîchit pour être sûr d'avoir la dernière version

    //         // ✅ Déclenche la génération du certificat automatiquement
    //         try {
    //             // On instancie le CertificateController pour appeler sa méthode generate()
    //             $certificateController = app(\App\Http\Controllers\Api\CertificateController::class);
                
    //             // On crée une fausse Request avec l'utilisateur actuel et l'URL du cours
    //             $request = new \Illuminate\Http\Request();
    //             $request->setUserResolver(fn() => $enrollment->user);
                
    //             $certificateController->generate($request, $enrollment->course->slug);
                
    //         } catch (\Throwable $e) {
    //             // On log l'erreur mais on ne bloque pas la progression
    //             \Log::error('Erreur génération auto certificat : ' . $e->getMessage());
    //         }
    //     }
    // }
    private function checkCourseCompletion(Enrollment $enrollment): void
{
    if (!$this->isCourseCompleted($enrollment)) {
        // Si le cours n'est plus complet, on retire le certificat
        if ($enrollment->completed_at) {
            $enrollment->update(['completed_at' => null, 'certificate_issued' => false]);
        }
        return;
    }

    // Le cours est complet
    if (!$enrollment->completed_at) {
        $enrollment->update(['completed_at' => now()]);
        $enrollment->refresh();

        try {
            $certificateController = app(\App\Http\Controllers\Api\CertificateController::class);
            $request = new \Illuminate\Http\Request();
            $request->setUserResolver(fn() => $enrollment->user);
            $certificateController->generate($request, $enrollment->course->slug);
        } catch (\Throwable $e) {
            \Log::error('Erreur génération auto certificat : ' . $e->getMessage());
        }
    }
}
}