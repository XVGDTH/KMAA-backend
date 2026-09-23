<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * GET /api/courses
     * Liste publique des cours (remplace le tableau COURSES codé en dur).
     */
    public function index(Request $request)
    {
        $query = Course::query()->with('instructor:id,name');

        if ($request->filled('subject')) {
            $query->where('subject', $request->string('subject'));
        }
        if ($request->filled('level')) {
            $query->where('level', $request->string('level'));
        }
        if ($request->boolean('popular')) {
            $query->where('popular', true);
        }

        $courses = $query->latest()->get();

        return response()->json([
            'data' => $courses->map(fn (Course $c) => $this->formatCourseSummary($c)),
        ]);
    }

    /**
     * GET /api/courses/{slug}
     * Détail complet d'un cours avec la STRUCTURE HEC :
     *   modules → chapters (Semaine N) → sequences
     *   + finalExam
     */
    public function show(string $slug)
    {
        $course = Course::with([
            'instructor:id,name,bio,avatar',
            'modules.chapters.sequences',
            'chapters.sequences',
            'finalExam',
        ])->where('slug', $slug)->first();

        if (! $course) {
            return response()->json(['message' => 'Cours introuvable.'], 404);
        }

        return response()->json([
            'data' => $this->formatCourseDetail($course),
        ]);
    }

    /**
     * GET /api/formateur/courses
     * Liste des cours du formateur connecté.
     */
    public function myCourses(Request $request)
    {
        $user = $request->user();

        $query = Course::with('instructor:id,name')
            ->withCount('chapters');

        if (! $user->hasRole('admin')) {
            $query->where('instructor_id', $user->id);
        }

        $courses = $query->latest()->get();

        return response()->json([
            'data' => $courses->map(fn (Course $c) => array_merge(
                $this->formatCourseSummary($c),
                [
                    'description'   => $c->description,
                    'chaptersCount' => $c->chapters_count,
                ]
            )),
        ]);
    }

    // ============================================================
    // FORMATAGE
    // ============================================================

    private function formatCourseSummary(Course $course): array
    {
        return [
            'id'                  => $course->slug,
            'courseId'            => $course->id,
            'subject'             => $course->subject,
            'category'            => $course->category,
            'title'               => $course->title,
            'level'               => $course->level,
            'diploma'             => $course->diploma,
            'durationFull'        => $course->duration_full,
            'durationAlternance'  => $course->duration_alternance,
            'image'               => $course->image,
            'imageAlt'            => $course->image_alt,
            'popular'             => $course->popular,
            'badge'               => $course->badge,
            'isFree'              => $course->is_free,
            'hasCertificate'      => $course->has_certificate,
            'route'               => '/formations/' . $course->slug,
        ];
    }

    private function formatCourseDetail(Course $course): array
    {
        return array_merge($this->formatCourseSummary($course), [
            'slug'           => $course->slug,
            'description'    => $course->description,
            'instructor'     => $course->instructor?->name ?? 'Équipe pédagogique KMAA',
            'instructorRole' => $course->instructor?->bio ?? 'Formateurs experts du domaine',

            // 🆕 Structure HEC : modules → chapitres → séquences
            'modules' => $course->modules->map(function ($module) {
                return [
                    'id'          => $module->id,
                    'title'       => $module->title,
                    'description' => $module->description,
                    'position'    => $module->position,
                    'chapters'    => $module->chapters->map(fn ($ch) => [
                        'id'         => (string) $ch->id,
                        'title'      => $ch->title,
                        'weekNumber' => $ch->week_number,
                        'position'   => $ch->position,
                        'sequences'  => $ch->sequences
                            ->map(fn ($seq) => $this->formatSequence($seq))
                            ->values(),
                    ])->values(),
                    'moduleQuiz' => $module->moduleQuiz->first()
                        ? $this->formatSequence($module->moduleQuiz->first())
                        : null,
                ];
            })->values(),

            // Chapitres orphelins (sans module)
            'chapters' => $course->chapters
                ->whereNull('module_id')
                ->map(fn ($chapter) => [
                    'id'         => (string) $chapter->id,
                    'title'      => $chapter->title,
                    'weekNumber' => $chapter->week_number,
                    'position'   => $chapter->position,
                    'sequences'  => $chapter->sequences
                        ->map(fn ($seq) => $this->formatSequence($seq))
                        ->values(),
                ])->values(),

            // Examen final
            'finalExam' => $course->finalExam->first()
                ? $this->formatSequence($course->finalExam->first())
                : null,
        ]);
    }

    private function formatSequence($seq): array
    {
        return [
            'id'              => (string) $seq->id,
            'title'           => $seq->title,
            'type'            => $seq->type,
            'duration'        => $seq->duration,
            'videoUrl'        => $seq->video_url,
            'documentUrl'     => $seq->document_url,
            'instructions'    => $seq->instructions,
            'quizQuestions'   => $seq->quiz_questions,
            'passingScore'    => $seq->passing_score,
            'isBlocking'      => (bool) $seq->is_blocking,
            'isFinal'         => (bool) $seq->is_final,
            'deadline'        => $seq->deadline?->toIso8601String(),
            'allowFileUpload' => (bool) $seq->allow_file_upload,
            'position'        => $seq->position,
        ];
    }
}