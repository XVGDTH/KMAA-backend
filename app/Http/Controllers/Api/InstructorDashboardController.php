<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstructorDashboardController extends Controller
{
    // ============================================================
    // GET /api/formateur/dashboard — 🔒 auth:sanctum
    // ============================================================
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Liste des cours du formateur
        $courses = Course::where('instructor_id', $user->id)
            ->withCount(['enrollments', 'chapters'])
            ->get();

        $courseIds = $courses->pluck('id');

        // 2. Statistiques globales
        $totalStudents = Enrollment::whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id');
        $totalEnrollments = Enrollment::whereIn('course_id', $courseIds)->count();

        $completedEnrollments = Enrollment::whereIn('course_id', $courseIds)
            ->where('certificate_issued', true)
            ->count();

        $completionRate = $totalEnrollments > 0
            ? round(($completedEnrollments / $totalEnrollments) * 100, 1)
            : 0;

            $pendingCount = AssignmentSubmission::whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
    ->where('status', 'soumis')
    ->count();

        // 3. Travaux à corriger
        $pendingSubmissions = AssignmentSubmission::with(['user:id,name', 'sequence:id,title,type', 'course:id,title'])
            ->whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
            ->where('status', 'soumis')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $gradedSubmissions = AssignmentSubmission::whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
            ->whereIn('status', ['corrige', 'retourne'])
            ->count();

        // 4. Moyenne des notes
        $averageGrade = AssignmentSubmission::whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
            ->whereNotNull('grade')
            ->avg('grade');

        // 5. Sessions live à venir
        $upcomingLiveSessions = LiveSession::whereIn('course_id', $courseIds)
            ->where('is_live', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'courseId' => $s->course_id,
                'roomName' => $s->room_name,
                'scheduledAt' => $s->created_at?->toIso8601String(),
            ]);

        // 6. Activité récente (dernières inscriptions)
        $recentEnrollments = Enrollment::with(['user:id,name', 'course:id,title'])
            ->whereIn('course_id', $courseIds)
            ->orderByDesc('enrolled_at')
            ->limit(10)
            ->get()
            ->map(fn($e) => [
                'studentName' => $e->user?->name ?? 'Apprenant',
                'courseTitle' => $e->course?->title ?? '',
                'enrolledAt' => $e->enrolled_at?->toIso8601String(),
            ]);

        // 7. Répartition des cours (brouillon/publié)
        $coursesStats = $courses->map(fn($c) => [
            'id' => $c->id,
            'title' => $c->title,
            'type' => $c->type ?? 'individual',
            'image' => $c->image,
            'enrolledCount' => $c->enrollments_count ?? 0,
            'chaptersCount' => $c->chapters_count ?? 0,
        ]);

        // ============================================================
        // ✅ 8. Derniers avis des apprenants (DÉPLACÉ AVANT LE RETURN)
        // ============================================================
        $recentFeedbacks = DB::table('feedbacks')
            ->join('sequences', 'sequences.id', '=', 'feedbacks.sequence_id')
            ->join('chapters', 'chapters.id', '=', 'sequences.chapter_id')
            ->join('courses', 'courses.id', '=', 'chapters.course_id')
            ->join('users', 'users.id', '=', 'feedbacks.user_id')
            ->where('courses.instructor_id', $user->id)
            ->orderByDesc('feedbacks.created_at')
            ->limit(5)
            ->get([
                'feedbacks.id',
                'feedbacks.comment',
                'feedbacks.rating',
                'users.name as studentName',
                'courses.title as courseTitle',
                'feedbacks.created_at',
            ]);

        // ============================================================
        // ✅ 9. Dernières discussions (forum) (DÉPLACÉ AVANT LE RETURN)
        // ============================================================
        $recentDiscussions = DB::table('forum_threads')
            ->join('courses', 'courses.id', '=', 'forum_threads.course_id')
            ->join('users', 'users.id', '=', 'forum_threads.user_id')
            ->where('courses.instructor_id', $user->id)
            ->orderByDesc('forum_threads.created_at')
            ->limit(5)
            ->get([
                'forum_threads.id',
                'forum_threads.title',
                'users.name as authorName',
                'courses.title as courseTitle',
                'forum_threads.created_at',
            ]);

        // ============================================================
        // ✅ LE RETURN FINAL (avec toutes les données)
        // ============================================================
        return response()->json([
            'data' => [
                'stats' => [
                    'totalCourses' => $courses->count(),
                    'totalStudents' => $totalStudents,
                    'totalEnrollments' => $totalEnrollments,
                    'completionRate' => $completionRate,
                    // 'pendingSubmissions' => $pendingSubmissions->count(),
                    'pendingSubmissions' => $pendingCount,
                    'gradedSubmissions' => $gradedSubmissions,
                    'averageGrade' => $averageGrade ? round($averageGrade, 1) : null,
                ],
                'courses' => $coursesStats,
                'pendingSubmissions' => $pendingSubmissions->map(fn($s) => [
                    'id' => (string) $s->id,
                    'studentName' => $s->user?->name ?? 'Apprenant',
                    'sequenceTitle' => $s->sequence?->title ?? '',
                    'courseTitle' => $s->course?->title ?? '',
                    'fileUrl' => $s->file_path ? '/storage/' . $s->file_path : null,
                    'comment' => $s->comment,
                    'submittedAt' => $s->created_at?->format('d/m/Y H:i'),
                    'sequenceType' => $s->sequence?->type,
                ]),
                'upcomingLiveSessions' => $upcomingLiveSessions,
                'recentEnrollments' => $recentEnrollments,
                'recentFeedbacks' => $recentFeedbacks,
                'recentDiscussions' => $recentDiscussions,
            ],
        ]);
    }
}