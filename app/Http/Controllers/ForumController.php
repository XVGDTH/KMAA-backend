<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    /**
     * Liste des discussions d'un cours
     */
    public function index($courseId)
    {
        $threads = ForumThread::with([
            'user:id,name',
            'chapter:id,title',
            'replies.user:id,name',
        ])
        ->where('course_id', $courseId)
        ->latest()
        ->get();

        return response()->json([
            'data' => $threads
        ]);
    }


    /**
     * Créer une discussion
     */
    public function storeThread(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'chapter_id' => 'nullable|integer|exists:chapters,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $thread = ForumThread::create([
            'course_id' => $validated['course_id'],
            'user_id' => Auth::id(),
            'chapter_id' => $validated['chapter_id'] ?? null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'upvotes' => 0,
            'resolved' => false,
        ]);

        $thread->load([
            'user:id,name',
            'chapter:id,title',
            'replies.user:id,name',
        ]);

        // ✅ NOTIFICATION : nouvelle question → le formateur du cours
        $course    = Course::find($validated['course_id']);
        $instructor = $course?->instructor_id
            ? \App\Models\User::find($course->instructor_id)
            : null;

        \App\Services\NotificationService::send(
            'forum_question',
            'Nouvelle question sur « ' . ($course?->title ?? 'un cours') . ' »',
            (Auth::user()?->name ?? 'Un apprenant') . ' a posé une question : ' . Str::limit($validated['title'], 80),
            $instructor,                             // destinataire = le formateur
            $course?->slug,
        );

        return response()->json([
            'data' => $thread
        ], 201);
    }


    /**
     * Ajouter une réponse
     */
    public function storeReply(
        Request $request,
        ForumThread $thread
    ) {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $reply = ForumReply::create([
            'forum_thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'upvotes' => 0,
        ]);

        $reply->load([
            'user:id,name',
        ]);

        // ✅ NOTIFICATION : réponse → l'auteur de la question
        \App\Services\NotificationService::send(
            'forum_reply',
            'Nouvelle réponse à ta question',
            (Auth::user()?->name ?? 'Quelqu\'un') . ' a répondu à ta question « ' . Str::limit($thread->title, 60) . ' »',
            $thread->user,                           // destinataire = l'auteur de la discussion
            $thread->course?->slug ?? null,
        );

        return response()->json([
            'data' => $reply
        ], 201);
    }


    /**
     * Voter pour une discussion
     */
    public function upvoteThread(ForumThread $thread)
    {
        $thread->increment('upvotes');

        $thread->refresh();

        $thread->load([
            'user:id,name',
            'chapter:id,title',
            'replies.user:id,name',
        ]);

        return response()->json([
            'data' => $thread
        ]);
    }


    /**
     * Voter pour une réponse
     */
    public function upvoteReply(ForumReply $reply)
    {
        $reply->increment('upvotes');

        $reply->refresh();

        $reply->load([
            'user:id,name',
        ]);

        return response()->json([
            'data' => $reply
        ]);
    }


    /**
     * Marquer / démarquer une discussion comme résolue
     */
    public function toggleResolved(ForumThread $thread)
    {
        $user = Auth::user();

        if (
            $thread->user_id !== $user->id &&
            ! $user->hasRole('formateur')
        ) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à modifier cette discussion.'
            ], 403);
        }

        $thread->update([
            'resolved' => ! $thread->resolved,
        ]);

        $thread->refresh();

        $thread->load([
            'user:id,name',
            'chapter:id,title',
            'replies.user:id,name',
        ]);

        return response()->json([
            'data' => $thread
        ]);
    }

    /**
     * Discussions des cours du formateur connecté
     */
    public function instructorForum(Request $request)
    {
        $user = $request->user();

        $courseIds = Course::where(
            'instructor_id',
            $user->id
        )->pluck('id');

        $threads = ForumThread::with([
            'course:id,slug,title',
            'user:id,name',
            'chapter:id,title',
            'replies.user:id,name',
        ])
            ->whereIn('course_id', $courseIds)
            ->latest()
            ->get();

        return response()->json([
            'data' => $threads,
        ]);
    }
}