<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Enrollment, Sequence, SequenceProgress};
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    /** Seuil de réussite d'un quiz (%). */
    private const QUIZ_PASS_THRESHOLD = 85;

    // ============================================================
    // POST /api/sequences/{sequenceId}/complete
    // - Séquence simple : marque terminé
    // - SÉQUENCE QUIZ : reçoit les réponses, CORRIGE côté serveur,
    //   calcule le score, et ne marque "complété" QUE si ≥ 85%.
    // ============================================================
    public function complete(Request $request, int $sequenceId)
    {
        $sequence = Sequence::with('chapter.course')->findOrFail($sequenceId);

        $enrollment = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $sequence->chapter->course_id)
            ->firstOrFail();

        // --------------------------------------------------------
        // CAS 1 : SÉQUENCE QUIZ → scoring server-side
        // --------------------------------------------------------
        if ($sequence->type === 'quiz') {

            $questions = is_string($sequence->quiz_questions)
                ? json_decode($sequence->quiz_questions, true)
                : $sequence->quiz_questions;

            if (empty($questions)) {
                return response()->json(['message' => 'Ce quiz n\'a pas encore de questions.'], 422);
            }

            $answers = $request->validate([
                'answers'                 => ['required', 'array'],
                'answers.*'               => ['nullable'],
            ])['answers'];

            $correctCount = 0;

            foreach ($questions as $i => $question) {
                $given = $answers[$i] ?? null;

                if (($question['type'] ?? 'qcm') === 'texte') {
                    // TEXTE : comparaison souple
                    $expected = strtolower(trim($question['expectedAnswer'] ?? ''));
                    if ($expected !== '' && strtolower(trim((string) $given)) === $expected) {
                        $correctCount++;
                    }
                } else {
                    // QCM : comparaison d'index
                    if ((string) ($question['correctIndex'] ?? '') === (string) $given) {
                        $correctCount++;
                    }
                }
            }

            $scorePercent = (int) round($correctCount / count($questions) * 100);
            $passed = $scorePercent >= self::QUIZ_PASS_THRESHOLD;

            $progress = SequenceProgress::updateOrCreate(
                ['enrollment_id' => $enrollment->id, 'sequence_id' => $sequence->id],
                [
                    'score_percent' => $scorePercent,
                    'passed'        => $passed,
                    'attempts'      => $enrollment ? ($progress->attempts ?? 0) + 1 : 1,
                    // ⚠️ 'completed' n'est marqué QUE si le quiz est passé !
                    'completed'     => $passed,
                    'completed_at'  => $passed ? now() : null,
                ]
            );

            // 🔔 Notifie le premier passage de quiz (optionnel, une fois)
            if ($passed && ! $enrollment->last_accessed_at) {
                // (le hook « fin de parcours » plus bas gérera le reste)
            }

            $this->checkCourseCompletion($enrollment);

            return response()->json([
                'data' => [
                    // ⚠️ NE RENVOIE JAMAIS les bonnes réponses avant validation !
                    'scorePercent' => $scorePercent,
                    'passed'       => $passed,
                    'threshold'    => self::QUIZ_PASS_THRESHOLD,
                    'progressPercent' => $enrollment->progressPercent(),
                    'courseCompleted' => $completedSequences >= $totalSequences && $totalSequences > 0,
                ],
                // Si échec : l'apprenant retente (pas de correction révélée)
            ]);
        }

        // --------------------------------------------------------
        // CAS 2 : SÉQUENCE SIMPLE (video, document...) → marque terminé
        // --------------------------------------------------------
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
    // GET /api/courses/{slugOrId}/quiz-state — l'état du quiz
    // (l'apprenant doit savoir s'il a déjà passé / son meilleur score)
    // ============================================================
    public function quizState(Request $request, int $sequenceId)
    {
        $enrollment = Enrollment::where('user_id', $request->user()->id)->firstOrFail();

        $progress = SequenceProgress::where('enrollment_id', $enrollment->id)
            ->where('sequence_id', $sequenceId)
            ->first();

        return response()->json([
            'data' => [
                'scorePercent' => $progress?->score_percent,
                'passed'       => (bool) $progress?->passed,
                'attempts'     => $progress?->attempts ?? 0,
            ],
        ]);
    }

    // ============================================================
    // HELPERS — détection de fin de parcours
    // ============================================================

    private function isCourseCompleted(Enrollment $enrollment): bool
    {
        $total = \App\Models\Sequence::whereHas('chapter', function ($q) use ($enrollment) {
            $q->where('course_id', $enrollment->course_id);
        })->count();

        if ($total === 0) return false;

        $done = SequenceProgress::where('enrollment_id', $enrollment->id)
            ->where('completed', true)
            ->whereIn('sequence_id', \App\Models\Sequence::whereHas('chapter', function ($q) use ($enrollment) {
                $q->where('course_id', $enrollment->course_id);
            })->pluck('id'))
            ->count();

        return $done >= $total;
    }

    private function checkCourseCompletion(Enrollment $enrollment): void
    {
        if ($this->isCourseCompleted($enrollment) && ! $enrollment->completed_at) {
            $enrollment->update(['completed_at' => now()]);
        }
    }

    // ============================================================
    // GET /api/courses/{slug}/enrollment-progress
    // ============================================================
    public function show(Request $request, string $courseSlug)
    {
        $enrollment = Enrollment::whereHas('course', fn ($q) => $q->where('slug', $courseSlug))
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $enrollment->update(['last_accessed_at' => now()]);

        return response()->json([
            'data' => ['progressPercent' => $enrollment->progressPercent()],
        ]);
    }
}