<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Module;
use App\Models\Sequence;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FormateurContentController extends Controller
{
    // ========================================================
    // AFFICHER LE CONTENU COMPLET DU COURS
    // GET /api/formateur/courses/{course}/content
    // ========================================================
    public function content(Request $request, Course $course)
    {
        $this->authorizeCourse($request, $course);

        $course->load([
            'modules.chapters.sequences',
            'chapters.sequences',          // chapitres orphelins (module_id null)
            'finalExam',                   // examen final (is_final = true)
        ]);

        // Quiz de fin de module : séquences avec module_id rempli et chapter_id null
        $moduleQuizzes = Sequence::whereIn('module_id', $course->modules->pluck('id'))
            ->whereNull('chapter_id')
            ->where('type', 'quiz')
            ->get()
            ->groupBy('module_id');

        return response()->json([
            'data' => [
                'course' => [
                    'id'    => $course->id,
                    'slug'  => $course->slug,
                    'title' => $course->title,
                ],

                'modules' => $course->modules->map(function (Module $m) use ($moduleQuizzes) {
                    $moduleQuiz = $moduleQuizzes->get($m->id, collect())->first();

                    return [
                        'id'          => $m->id,
                        'courseId'    => $m->course_id,
                        'title'       => $m->title,
                        'description' => $m->description,
                        'position'    => $m->position,
                        'chapters'    => $m->chapters->map(
                            fn (Chapter $c) => $this->formatChapter($c)
                        )->values(),
                        'moduleQuiz'  => $moduleQuiz
                            ? $this->formatSequence($moduleQuiz)
                            : null,
                    ];
                })->values(),

                // Chapitres orphelins (ancienne structure sans module)
                'chapters' => $course->chapters
                    ->whereNull('module_id')
                    ->map(fn (Chapter $c) => $this->formatChapter($c))
                    ->values(),

                // Examen final du cours (is_final = true)
                'finalExam' => $course->finalExam->first()
                    ? $this->formatSequence($course->finalExam->first())
                    : null,
            ],
        ]);
    }

    // ========================================================
    // CRÉER UN CHAPITRE
    // POST /api/formateur/courses/{course}/chapters
    // body: { title, module_id?, week_number?, position? }
    // ========================================================
    public function storeChapter(Request $request, Course $course)
    {
        $this->authorizeCourse($request, $course);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'module_id'   => ['nullable', 'integer', 'exists:modules,id'],
            'week_number' => ['nullable', 'integer', 'min:1', 'max:52'],
            'position'    => ['nullable', 'integer', 'min:0'],
        ]);

        $position = $validated['position']
            ?? (($course->chapters()->max('position') ?? -1) + 1);

        $chapter = $course->chapters()->create([
            'title'       => $validated['title'],
            'module_id'   => $validated['module_id'] ?? null,
            'week_number' => $validated['week_number'] ?? null,
            'position'    => $position,
        ]);

        $chapter->load('sequences');

        return response()->json([
            'message' => 'Chapitre créé avec succès.',
            'data'    => $this->formatChapter($chapter),
        ], 201);
    }

    // ========================================================
    // MODIFIER UN CHAPITRE
    // PATCH /api/formateur/chapters/{chapter}
    // ========================================================
    public function updateChapter(Request $request, Chapter $chapter)
    {
        $this->authorizeCourse($request, $chapter->course);

        $validated = $request->validate([
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'module_id'   => ['nullable', 'integer', 'exists:modules,id'],
            'week_number' => ['nullable', 'integer', 'min:1', 'max:52'],
            'position'    => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        $chapter->update($validated);
        $chapter->load('sequences');

        return response()->json([
            'message' => 'Chapitre modifié avec succès.',
            'data'    => $this->formatChapter($chapter),
        ]);
    }

    // ========================================================
    // SUPPRIMER UN CHAPITRE
    // DELETE /api/formateur/chapters/{chapter}
    // ========================================================
    public function destroyChapter(Request $request, Chapter $chapter)
    {
        $this->authorizeCourse($request, $chapter->course);

        foreach ($chapter->sequences as $sequence) {
            $this->deleteSequenceFiles($sequence);
        }

        $chapter->delete();

        return response()->json([
            'message' => 'Chapitre supprimé avec succès.',
        ]);
    }

    // ========================================================
    // CRÉER UNE SÉQUENCE
    // POST /api/formateur/chapters/{chapter}/sequences
    // OU POST /api/formateur/modules/{module}/sequences   (quiz de module)
    // OU POST /api/formateur/courses/{course}/final-exam  (examen final)
    // ========================================================
    public function storeSequence(Request $request, Chapter $chapter)
    {
        $course = $chapter->course;
        $this->authorizeCourse($request, $course);

        $validated = $this->validateSequence($request);

        $position = $validated['position']
            ?? (($chapter->sequences()->max('position') ?? -1) + 1);

        $videoUrl = $request->hasFile('video')
            ? $this->storeFile($request->file('video'), 'courses/' . $course->id . '/videos')
            : null;

        $documentUrl = $request->hasFile('document')
            ? $this->storeFile($request->file('document'), 'courses/' . $course->id . '/documents')
            : null;

        $quiz = $validated['quiz_questions'] ?? null;
        if (is_string($quiz)) {
            $quiz = json_decode($quiz, true);
        }

        $sequence = $chapter->sequences()->create([
            'title'            => $validated['title'],
            'type'             => $validated['type'],
            'duration'         => $validated['duration'] ?? null,
            'video_url'        => $videoUrl ?? ($validated['video_url'] ?? null),
            'document_url'     => $documentUrl ?? ($validated['document_url'] ?? null),
            'quiz_questions'   => $quiz,
            'instructions'     => $validated['instructions'] ?? null,
            'passing_score'    => $validated['passing_score'] ?? null,
            'is_blocking'      => $validated['is_blocking'] ?? false,
            'deadline'         => $validated['deadline'] ?? null,
            'allow_file_upload'=> $validated['allow_file_upload'] ?? false,
            'position'         => $position,
        ]);

        return response()->json([
            'message' => 'Séquence créée avec succès.',
            'data'    => $this->formatSequence($sequence),
        ], 201);
    }

    // ========================================================
    // CRÉER UN QUIZ DE FIN DE MODULE
    // POST /api/formateur/modules/{module}/quiz
    // ========================================================
    public function storeModuleQuiz(Request $request, Module $module)
    {
        $course = $module->course;
        $this->authorizeCourse($request, $course);

        $validated = $this->validateSequence($request);

        if ($validated['type'] !== 'quiz') {
            return response()->json([
                'message' => 'Le type doit être "quiz" pour un quiz de module.',
            ], 422);
        }

        $quiz = $validated['quiz_questions'] ?? null;
        if (is_string($quiz)) {
            $quiz = json_decode($quiz, true);
        }

        // Upsert : un seul quiz de module à la fois
        $sequence = Sequence::updateOrCreate(
            [
                'module_id'  => $module->id,
                'chapter_id' => null,
                'type'       => 'quiz',
            ],
            [
                'title'            => $validated['title'],
                'quiz_questions'   => $quiz,
                'passing_score'    => $validated['passing_score'] ?? 85,
                'is_blocking'      => $validated['is_blocking'] ?? true,
                'instructions'     => $validated['instructions'] ?? null,
                'position'         => 0,
            ]
        );

        return response()->json([
            'message' => 'Quiz de module enregistré.',
            'data'    => $this->formatSequence($sequence),
        ], 201);
    }

    // ========================================================
    // CRÉER L'EXAMEN FINAL DU COURS
    // POST /api/formateur/courses/{course}/final-exam
    // ========================================================
    public function storeFinalExam(Request $request, Course $course)
    {
        $this->authorizeCourse($request, $course);

        $validated = $this->validateSequence($request);

        if ($validated['type'] !== 'quiz') {
            return response()->json([
                'message' => 'Le type doit être "quiz" pour l\'examen final.',
            ], 422);
        }

        $quiz = $validated['quiz_questions'] ?? null;
        if (is_string($quiz)) {
            $quiz = json_decode($quiz, true);
        }

        $sequence = Sequence::updateOrCreate(
            [
                'course_id'  => $course->id,
                'module_id'  => null,
                'chapter_id' => null,
                'is_final'   => true,
            ],
            [
                'title'            => $validated['title'] ?? 'Examen final',
                'type'             => 'quiz',
                'quiz_questions'   => $quiz,
                'passing_score'    => $validated['passing_score'] ?? 70,
                'is_blocking'      => true,
                'instructions'     => $validated['instructions'] ?? null,
                'position'         => 0,
            ]
        );

        return response()->json([
            'message' => 'Examen final enregistré.',
            'data'    => $this->formatSequence($sequence),
        ], 201);
    }

    // ========================================================
    // MODIFIER UNE SÉQUENCE
    // PATCH /api/formateur/sequences/{sequence}
    // ========================================================
    public function updateSequence(Request $request, Sequence $sequence)
    {
        $course = $sequence->chapter?->course
            ?? $sequence->module?->course
            ?? $sequence->course;

        if (! $course) {
            abort(404, 'Cours introuvable pour cette séquence.');
        }

        $this->authorizeCourse($request, $course);

        $validated = $this->validateSequence($request, true);

        $videoUrl    = $sequence->video_url;
        $documentUrl = $sequence->document_url;

        if ($request->hasFile('video')) {
            $this->deleteFile($sequence->video_url);
            $videoUrl = $this->storeFile($request->file('video'), 'courses/' . $course->id . '/videos');
        }

        if ($request->hasFile('document')) {
            $this->deleteFile($sequence->document_url);
            $documentUrl = $this->storeFile($request->file('document'), 'courses/' . $course->id . '/documents');
        }

        $data = [];

        foreach (['title', 'type', 'duration', 'instructions', 'position'] as $field) {
            if (array_key_exists($field, $validated)) {
                $data[$field] = $validated[$field];
            }
        }

        if (array_key_exists('passing_score', $validated)) {
            $data['passing_score'] = $validated['passing_score'];
        }
        if (array_key_exists('is_blocking', $validated)) {
            $data['is_blocking'] = (bool) $validated['is_blocking'];
        }
        if (array_key_exists('deadline', $validated)) {
            $data['deadline'] = $validated['deadline'];
        }
        if (array_key_exists('allow_file_upload', $validated)) {
            $data['allow_file_upload'] = (bool) $validated['allow_file_upload'];
        }

        if (array_key_exists('video_url', $validated) || $request->hasFile('video')) {
            $data['video_url'] = $videoUrl ?? ($validated['video_url'] ?? null);
        }
        if (array_key_exists('document_url', $validated) || $request->hasFile('document')) {
            $data['document_url'] = $documentUrl ?? ($validated['document_url'] ?? null);
        }

        if (array_key_exists('quiz_questions', $validated)) {
            $quiz = $validated['quiz_questions'];
            if (is_string($quiz)) {
                $quiz = json_decode($quiz, true);
            }
            $data['quiz_questions'] = $quiz;
        }

        $sequence->update($data);

        return response()->json([
            'message' => 'Séquence modifiée avec succès.',
            'data'    => $this->formatSequence($sequence->fresh()),
        ]);
    }

    // ========================================================
    // SUPPRIMER UNE SÉQUENCE
    // DELETE /api/formateur/sequences/{sequence}
    // ========================================================
    public function destroySequence(Request $request, Sequence $sequence)
    {
        $course = $sequence->chapter?->course
            ?? $sequence->module?->course
            ?? $sequence->course;

        if (! $course) {
            abort(404, 'Cours introuvable pour cette séquence.');
        }

        $this->authorizeCourse($request, $course);

        $this->deleteSequenceFiles($sequence);
        $sequence->delete();

        return response()->json([
            'message' => 'Séquence supprimée avec succès.',
        ]);
    }

    // ========================================================
    // VALIDATION D'UNE SÉQUENCE
    // ========================================================
    private function validateSequence(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'title' => [$required, 'string', 'max:255'],

            'type' => [
                $required,
                Rule::in([
                    'video',
                    'document',
                    'tp',
                    'td',
                    'cas_pratique',
                    'quiz',
                ]),
            ],

            'duration'         => ['nullable', 'string', 'max:255'],
            'video_url'        => ['nullable', 'string', 'max:2048'],
            'video'            => [
                'nullable', 'file',
                'mimetypes:video/mp4,video/webm,video/quicktime,video/x-msvideo',
                'max:512000',
            ],

            'document_url'     => ['nullable', 'string', 'max:2048'],
            'document'         => [
                'nullable', 'file',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx',
                'max:51200',
            ],

            // Quiz (string JSON venant du FormData Angular)
            'quiz_questions'   => ['nullable', 'string', 'max:65535'],

            'instructions'     => ['nullable', 'string'],

            // Quiz : options
            'passing_score'    => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_blocking'      => ['nullable', 'boolean'],

            // Cas pratique : options
            'deadline'         => ['nullable', 'date'],
            'allow_file_upload'=> ['nullable', 'boolean'],

            'position'         => ['nullable', 'integer', 'min:0'],
        ]);
    }

    // ========================================================
    // AUTORISATION
    // ========================================================
    private function authorizeCourse(Request $request, Course $course): void
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Utilisateur non authentifié.');
        }

        $isAdmin = method_exists($user, 'hasRole')
            ? $user->hasRole('admin')
            : ($user->role ?? null) === 'admin';

        $isOwner = (int) $course->instructor_id === (int) $user->id;

        if (! $isAdmin && ! $isOwner) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier ce cours.');
        }
    }

    // ========================================================
    // FICHIERS
    // ========================================================
    private function storeFile(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');
        return '/storage/' . $path;
    }

    private function deleteFile(?string $url): void
    {
        if (! $url) return;

        $prefix = '/storage/';
        $pos = strpos($url, $prefix);
        if ($pos === false) return;

        $path = substr($url, $pos + strlen($prefix));
        if ($path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

    private function deleteSequenceFiles(Sequence $sequence): void
    {
        $this->deleteFile($sequence->video_url);
        $this->deleteFile($sequence->document_url);
    }

    // ========================================================
    // FORMATAGE
    // ========================================================
    private function formatChapter(Chapter $chapter): array
    {
        return [
            'id'         => $chapter->id,
            'courseId'   => $chapter->course_id,
            'moduleId'   => $chapter->module_id,
            'title'      => $chapter->title,
            'weekNumber' => $chapter->week_number,
            'position'   => $chapter->position,
            'sequences'  => $chapter->sequences
                ->map(fn (Sequence $s) => $this->formatSequence($s))
                ->values(),
        ];
    }

    private function formatSequence(Sequence $sequence): array
    {
        return [
            'id'              => $sequence->id,
            'chapterId'       => $sequence->chapter_id,
            'moduleId'        => $sequence->module_id,
            'courseId'        => $sequence->course_id,
            'title'           => $sequence->title,
            'type'            => $sequence->type,
            'duration'        => $sequence->duration,
            'videoUrl'        => $sequence->video_url,
            'documentUrl'     => $sequence->document_url,
            'quizQuestions'   => $sequence->quiz_questions,
            'instructions'    => $sequence->instructions,
            'passingScore'    => $sequence->passing_score,
            'isBlocking'      => $sequence->is_blocking,
            'isFinal'         => $sequence->is_final,
            'deadline'        => $sequence->deadline?->toIso8601String(),
            'allowFileUpload' => $sequence->allow_file_upload,
            'position'        => $sequence->position,
            'createdAt'       => $sequence->created_at,
            'updatedAt'       => $sequence->updated_at,
        ];
    }
}