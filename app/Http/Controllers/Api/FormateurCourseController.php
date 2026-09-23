<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormateurCourseController extends Controller
{
    // ========================================================
    // CRÉATION — POST /api/formateur/courses
    // ========================================================

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'title'             => ['required', 'string', 'min:3', 'max:255'],
            'subject'           => ['nullable', 'string', 'max:255'],
            'category'          => ['nullable', 'string', 'max:255'],
            'level'             => ['nullable', 'string', 'max:255'],
            'diploma'           => ['nullable', 'string', 'max:255'],
            'durationFull'      => ['nullable', 'string', 'max:255'],
            'durationAlternance'=> ['nullable', 'string', 'max:255'],
            'description'       => ['nullable', 'string', 'max:5000'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            
        ]);

        // Slug unique depuis le titre
        $slug = Str::slug($data['title']);
        $i = 1;
        while (Course::where('slug', $slug)->exists()) {
            $slug = Str::slug($data['title']) . '-' . (++$i);
        }

        // Image de couverture
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('courses', 'public');
            $data['image'] = '/storage/' . $path;
        }

        $course = Course::create([
            'title'    => $data['title'],
            'slug'     => $slug,
            'subject'  => $data['subject'] ?? null,
            'category' => $data['category'] ?? null,
            'level'    => $data['level'] ?? null,
            'diploma'  => $data['diploma'] ?? null,
            // ⚠️ ADAPTE selon tes colonnes SQL (snake_case ?)
            'duration_full'        => $data['durationFull'] ?? null,
            'duration_alternance'  => $data['durationAlternance'] ?? null,
            'description'          => $data['description'] ?? null,
            'image'                => $data['image'] ?? null,

            // ⚠️ ADAPTE : formateur_id OU user_id (voir CourseController@myCourses)
            // 'formateur_id' => $user->id,
            'instructor_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Cours créé avec succès.',
            'data' => [
                'id'    => $course->id,
                'slug'  => $course->slug,
                'title' => $course->title,
            ],
        ], 201);
    }

    // ========================================================
    // MISE À JOUR — PATCH /api/formateur/courses/{course}
    // ========================================================

    // public function update(Request $request, Course $course)
    // {
    //     $user = $request->user();

    //     // Sécurité : seul le propriétaire peut modifier
    //     // ⚠️ ADAPTE la colonne si besoin (formateur_id / user_id)
    //     if ($course->formateur_id !== $user->id && $user->role !== 'admin') {
    //         return response()->json(['message' => 'Non autorisé.'], 403);
    //     }

    //     $data = $request->validate([
    //         'title'             => ['sometimes', 'string', 'min:3', 'max:255'],
    //         'subject'           => ['nullable', 'string', 'max:255'],
    //         'category'          => ['nullable', 'string', 'max:255'],
    //         'level'             => ['nullable', 'string', 'max:255'],
    //         'diploma'           => ['nullable', 'string', 'max:255'],
    //         'durationFull'      => ['nullable', 'string', 'max:255'],
    //         'durationAlternance'=> ['nullable', 'string', 'max:255'],
    //         'description'       => ['nullable', 'string', 'max:5000'],
    //         'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
    //     ]);

    //     if ($request->hasFile('image')) {
    //         $path = $request->file('image')->store('courses', 'public');
    //         $data['image'] = '/storage/' . $path;
    //     }

    //     $course->update($data);

    //     return response()->json([
    //         'message' => 'Cours mis à jour.',
    //         'data'    => $course->fresh(),
    //     ]);
    // }

    public function update(Request $request, Course $course)
    {
        $user = $request->user();

        // Sécurité : seul le propriétaire (ou un admin) peut modifier
        if (
            ! $user->hasRole('admin')
            && (int) $course->instructor_id !== (int) $user->id
        ) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'title'             => ['sometimes', 'string', 'min:3', 'max:255'],
            'subject'           => ['nullable', 'string', 'max:255'],
            'category'          => ['nullable', 'string', 'max:255'],
            'level'             => ['nullable', 'string', 'max:255'],
            'diploma'           => ['nullable', 'string', 'max:255'],
            'durationFull'      => ['nullable', 'string', 'max:255'],
            'durationAlternance'=> ['nullable', 'string', 'max:255'],
            'description'       => ['nullable', 'string', 'max:5000'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
if ($request->hasFile('image')) {
            $path = $request->file('image')->store('courses', 'public');
            $data['image'] = '/storage/' . $path;
        }

        $course->update($data);

        return response()->json([
            'message' => 'Cours mis à jour.',
            'data'    => $course->fresh(),
        ]);
    }

    // ========================================================
    // SUPPRESSION — DELETE /api/formateur/courses/{course}
    // ========================================================

    public function destroy(Request $request, Course $course)
    {
        $user = $request->user();

        // ⚠️ ADAPTE la colonne si besoin
        // if ($course->formateur_id !== $user->id && $user->role !== 'admin') {
        if ((int) $course->instructor_id !== (int) $user->id && ! $user->hasRole('admin')) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $course->delete();

        return response()->json(['message' => 'Cours supprimé.']);
    }
}