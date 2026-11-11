<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Sequence;

class CourseController extends Controller
{
    /**
     * GET /api/courses
     * Liste publique des cours (remplace le tableau COURSES codé en dur).
     */
public function index(Request $request)
{
    $query = Course::query()->with('instructor:id,name');

    // ✅ Filtre par type (individual ou enterprise)
    $type = $request->query('type');
    if (in_array($type, ['individual', 'enterprise'])) {
        $query->where('type', $type);
    }

    // Filtres existants
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
public function show(Request $request, string $slugOrId)
{
    // ✅ Accepte à la fois le slug ET l'ID
    $course = Course::with([
        'instructor:id,name,bio,avatar',
        'modules.chapters.sequences',
        'chapters.sequences',
        'finalExam',
    ])
        ->where(function ($query) use ($slugOrId) {
            $query->where('slug', $slugOrId);
            if (is_numeric($slugOrId)) {
                $query->orWhere('id', (int) $slugOrId);
            }
        })
        ->first();

    if (! $course) {
        return response()->json(['message' => 'Cours introuvable.'], 404);
    }

    $userId = $request->user()?->id;

    return response()->json([
        'data' => $this->formatCourseDetail($course, $userId),
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
        'id'                 => $course->slug,
        'courseId'           => $course->id,
        'type'               => $course->type ?? 'individual',   // ✅ NOUVEAU
        'subject'            => $course->subject,
        'category'           => $course->category,
        'title'              => $course->title,
        'description'        => $course->description,
        'level'              => $course->level,
        'diploma'            => $course->diploma,
        'durationFull'       => $course->duration_full,
        'durationAlternance' => $course->duration_alternance,
        'image'              => $course->image,
       
        'imageAlt'           => $course->image_alt,
        'popular'            => $course->popular,
        'badge'              => $course->badge,
        'isFree'             => $course->is_free,
        'hasCertificate'     => $course->has_certificate,
        'price'              => (int) ($course->price ?? 0),
        'enterprise_base_price' => (int) ($course->enterprise_base_price ?? 0),
        'provider'           => $course->provider ?? 'KMAA Academy',
        'language'           => $course->language ?? 'Français',
        'startDate'          => $course->start_date,
        'deliveryMode'       => $course->delivery_mode,          // ✅ NOUVEAU
        'hoursPerWeek'       => $course->hours_per_week,         // ✅ NOUVEAU
        'modulesCount'       => $course->modules_count ?? 0,     // ✅ NOUVEAU
        'route'              => $course->type === 'enterprise'
            ? '/entreprises/formation/' . $course->id
            : '/sujets/cours/' . $course->slug,
    ];
}


// private function formatCourseDetail(Course $course, ?int $userId = null): array
// {
//     return array_merge($this->formatCourseSummary($course), [
//         'slug'           => $course->slug,
//         'description'    => $course->description,
//         'longDescription'=> $course->long_description,
//         'instructor'     => $course->instructor?->name ?? 'Expert KMAA',
//         'instructorRole' => $course->instructor?->bio ?? 'Formateur & Consultant',
//         'providerLogo'   => $course->instructor?->avatar,

//         // ============================================================
//         // ✅ STRUCTURE PLATE (compatible avec ton HTML actuel)
//         // On aplatit modules → chapters → sequences en tableau simple
//         // ============================================================
//        'modules' => $course->modules->map(function ($module) use ($userId) {
//     return [
//         'id' => $module->id,
//         'title' => $module->title,
//         'position' => $module->position,
//         'description' => $module->description,
//         'chapters' => $module->chapters->map(function ($chapter) use ($userId) {
//             return [
//                 'id' => (string) $chapter->id,
//                 'title' => $chapter->title,
//                 'weekNumber' => $chapter->week_number,
//                 'position' => $chapter->position,
//                 'sequences' => $chapter->sequences->map(function ($seq) use ($userId) {
//                     return $this->formatSequence($seq, $userId); // Utilise la méthode existante
//                 })->values(),
//             ];
//         })->values(),
//         'moduleQuiz' => null, // On verra ça plus tard
//     ];
// })->values(),

//         // Examen final
//         'finalExam' => $course->finalExam->first()
//             ? $this->formatSequence($course->finalExam->first(), $userId)
//             : null,
//     ]);
// }


private function formatCourseDetail(Course $course, ?int $userId = null): array
{
    // Quiz de fin de module : séquences avec module_id et sans chapter_id
    $moduleQuizzes = Sequence::whereIn('module_id', $course->modules->pluck('id'))
        ->whereNull('chapter_id')
        ->where('type', 'quiz')
        ->get()
        ->keyBy('module_id');

    return array_merge($this->formatCourseSummary($course), [
        'slug'           => $course->slug,
        'description'    => $course->description,
        'longDescription'=> $course->long_description,
        'instructor'     => $course->instructor?->name ?? 'Expert KMAA',
        'instructorRole' => $course->instructor?->bio ?? 'Formateur & Consultant',
        'providerLogo'   => $course->instructor?->avatar,

        'modules' => $course->modules->map(function ($module) use ($userId, $moduleQuizzes) {
            $quiz = $moduleQuizzes->get($module->id);

            return [
                'id'          => $module->id,
                'title'       => $module->title,
                'position'    => $module->position,
                'description' => $module->description,
                'chapters'    => $module->chapters->map(function ($chapter) use ($userId) {
                    return [
                        'id'         => (string) $chapter->id,
                        'title'      => $chapter->title,
                        'weekNumber' => $chapter->week_number,
                        'position'   => $chapter->position,
                        'sequences'  => $chapter->sequences
                            ->map(fn ($seq) => $this->formatSequence($seq, $userId))
                            ->values(),
                    ];
                })->values(),
                'moduleQuiz'  => $quiz ? $this->formatSequence($quiz, $userId) : null,   // ✅
            ];
        })->values(),

        'finalExam' => $course->finalExam->first()
            ? $this->formatSequence($course->finalExam->first(), $userId)
            : null,
    ]);
}

/**
 * ✅ Aplatit la structure HEC en un tableau simple :
 * [ { week: 1, title: "...", topics: [...] }, ... ]
 */
private function flattenModules(Course $course): array
{
    $result = [];
    $weekCounter = 1;

    foreach ($course->modules as $module) {
        $topics = [];

        foreach ($module->chapters as $chapter) {
            foreach ($chapter->sequences as $sequence) {
                $topics[] = $sequence->title;
            }
        }

        $result[] = [
            'week'   => $weekCounter,
            'title'  => $module->title,
            'topics' => $topics,
        ];

        $weekCounter++;
    }

    // Si pas de modules, fallback sur les chapitres orphelins
    if (empty($result)) {
        foreach ($course->chapters as $chapter) {
            $topics = [];
            foreach ($chapter->sequences as $sequence) {
                $topics[] = $sequence->title;
            }

            $result[] = [
                'week'   => $chapter->week_number ?? $weekCounter,
                'title'  => $chapter->title,
                'topics' => $topics,
            ];

            $weekCounter++;
        }
    }

    return $result;
}

    //          Chapitres orphelins (sans module)



private function formatSequence($seq, ?int $userId = null): array
{
    $completed = false;

    if ($userId) {
        // Récupérer l'enrollment de l'utilisateur pour ce cours
        $courseId = $seq->course_id
            ?? $seq->chapter?->course_id
            ?? $seq->module?->course_id;

        if ($courseId) {
            $enrollment = \DB::table('enrollments')
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->first();

            if ($enrollment) {
                $completed = \DB::table('sequence_progress')
                    ->where('enrollment_id', $enrollment->id)
                    ->where('sequence_id', $seq->id)
                    ->where('completed', true)
                    ->exists();
            }
        }
    }

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
        'completed'       => $completed,
    ];
}
}