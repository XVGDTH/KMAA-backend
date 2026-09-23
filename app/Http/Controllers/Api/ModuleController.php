<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    // ========================================================
    // CRÉER UN MODULE
    // POST /api/formateur/courses/{course}/modules
    // ========================================================
    public function store(Request $request, Course $course)
    {
        $this->authorizeCourse($request, $course);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'position'    => ['nullable', 'integer', 'min:0'],
        ]);

        $position = $validated['position']
            ?? (($course->modules()->max('position') ?? -1) + 1);

        $module = $course->modules()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'position'    => $position,
        ]);

        $module->load('chapters.sequences');

        return response()->json([
            'message' => 'Module créé avec succès.',
            'data'    => $this->formatModule($module),
        ], 201);
    }

    // ========================================================
    // MODIFIER UN MODULE
    // PATCH /api/formateur/modules/{module}
    // ========================================================
    public function update(Request $request, Module $module)
    {
        $this->authorizeCourse($request, $module->course);

        $validated = $request->validate([
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'position'    => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        $module->update($validated);

        $module->load('chapters.sequences');

        return response()->json([
            'message' => 'Module modifié avec succès.',
            'data'    => $this->formatModule($module),
        ]);
    }

    // ========================================================
    // SUPPRIMER UN MODULE
    // DELETE /api/formateur/modules/{module}
    //
    // ⚠️ Les chapitres ne sont PAS supprimés (module_id → null).
    // Les quiz de fin de module SONT supprimés (cascadeOnDelete).
    // ========================================================
    public function destroy(Request $request, Module $module)
    {
        $this->authorizeCourse($request, $module->course);

        // Supprime les fichiers associés aux quiz de fin de module
        $moduleQuiz = $module->moduleQuiz()->with('chapter')->get();
        foreach ($moduleQuiz as $quiz) {
            $this->deleteSequenceFiles($quiz);
        }

        $module->delete();

        return response()->json([
            'message' => 'Module supprimé. Les chapitres ont été détachés.',
        ]);
    }

    // ========================================================
    // HELPERS
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

    private function formatModule(Module $module): array
    {
        return [
            'id'          => $module->id,
            'courseId'    => $module->course_id,
            'title'       => $module->title,
            'description' => $module->description,
            'position'    => $module->position,
            'chapters'    => $module->chapters->map(fn ($ch) => [
                'id'          => $ch->id,
                'courseId'    => $ch->course_id,
                'moduleId'    => $ch->module_id,
                'title'       => $ch->title,
                'weekNumber'  => $ch->week_number,
                'position'    => $ch->position,
                'sequences'   => $ch->sequences->map(fn ($seq) => [
                    'id'            => $seq->id,
                    'chapterId'     => $seq->chapter_id,
                    'moduleId'      => $seq->module_id,
                    'courseId'      => $seq->course_id,
                    'title'         => $seq->title,
                    'type'          => $seq->type,
                    'duration'      => $seq->duration,
                    'videoUrl'      => $seq->video_url,
                    'documentUrl'   => $seq->document_url,
                    'quizQuestions' => $seq->quiz_questions,
                    'instructions'  => $seq->instructions,
                    'passingScore'  => $seq->passing_score,
                    'isBlocking'    => $seq->is_blocking,
                    'isFinal'       => $seq->is_final,
                    'deadline'      => $seq->deadline?->toIso8601String(),
                    'allowFileUpload' => $seq->allow_file_upload,
                    'position'      => $seq->position,
                ])->values(),
            ])->values(),
        ];
    }

    private function deleteSequenceFiles($sequence): void
    {
        // Utilise le même helper que FormateurContentController
        foreach ([$sequence->video_url, $sequence->document_url] as $url) {
            if (! $url) continue;

            $prefix = '/storage/';
            $pos = strpos($url, $prefix);
            if ($pos === false) continue;

            $path = substr($url, $pos + strlen($prefix));
            if ($path !== '') {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        }
    }
}