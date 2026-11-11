<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Course, LiveSession};
use App\Services\AccessService;
use App\Services\ZoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiveSessionController extends Controller
{
    public function __construct(
        private readonly AccessService $access,
        private readonly ZoomService $zoom,
    ) {}

    // ============================================================
    // POST /api/formateur/courses/{courseId}/live/start
    // ============================================================
    public function start(Request $request, string $courseId)
    {
        // 1. Résoudre le cours (slug OU id)
        $course = Course::where('slug', $courseId)->first()
            ?? Course::find($courseId);

        if (! $course) {
            return response()->json(['message' => 'Cours introuvable.'], 404);
        }

        // 2. Créer la réunion Zoom
        $meeting = $this->zoom->createMeeting(
            topic: "Séance live — {$course->title}",
            courseId: (string) $course->id,
        );

        if (! $meeting) {
            return response()->json([
                'message' => 'Impossible de créer la réunion Zoom. Vérifiez la configuration.',
            ], 500);
        }

        // 3. Sauvegarder en base
        $session = LiveSession::updateOrCreate(
            ['course_id' => $courseId],
            [
                'is_live'         => true,
                'instructor_id'   => $request->user()->id,
                'room_name'       => 'kmaa-live-' . Str::slug($courseId),  // gardé pour compat
                'zoom_meeting_id' => $meeting['id'],
                'zoom_start_url'  => $meeting['start_url'],
                'zoom_join_url'   => $meeting['join_url'],
                'zoom_password'   => $meeting['password'],
                'started_at'      => now(),
            ]
        );

        // 4. Notifier les inscrits
        $students = \App\Models\Enrollment::where('course_id', $course->id)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        \App\Services\NotificationService::send(
            'live_started',
            '🔴 Séance en direct maintenant !',
            'Le formateur a démarré la séance en direct. Rejoins-la depuis ton espace de cours.',
            $students,
            $courseId,
        );

        return response()->json([
            'data' => [
                'courseId'  => $session->course_id,
                'isLive'    => $session->is_live,
                'hostUrl'   => $session->zoom_start_url,
                'startedAt' => $session->started_at,
            ],
        ]);
    }

    // ============================================================
    // POST /api/formateur/courses/{courseId}/live/stop
    // ============================================================
    public function stop(string $courseId)
    {
        $session = LiveSession::where('course_id', $courseId)->first();

        if (! $session) {
            return response()->json(['message' => 'Aucune session active.'], 404);
        }

        // Terminer la réunion Zoom si elle existe
        if ($session->hasZoom()) {
            $this->zoom->endMeeting($session->zoom_meeting_id);
        }

        $session->update([
            'is_live'         => false,
            'zoom_meeting_id' => null,
            'zoom_start_url'  => null,
            'zoom_join_url'   => null,
            'zoom_password'   => null,
        ]);

        return response()->json(['message' => 'Séance terminée.']);
    }

    // ============================================================
    // GET /api/live-sessions/{courseId} — PUBLIC (indicateur)
    // ============================================================
    public function status(string $courseId)
    {
        $session = LiveSession::where('course_id', $courseId)->first();

        return response()->json([
            'data' => [
                'isLive'    => (bool) $session?->is_live,
                'startedAt' => $session?->started_at,
                // ⚠️ Les URLs Zoom ne sont JAMAIS exposées ici (sécurité)
            ],
        ]);
    }

    // ============================================================
    // GET /api/live-sessions/{courseId}/join — 🔒 GATED (level 2+)
    // ============================================================
    public function join(Request $request, string $courseId)
    {
        $session = LiveSession::where('course_id', $courseId)
            ->where('is_live', true)
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Aucune séance en direct pour ce cours.'], 404);
        }

        $course = Course::where('slug', $courseId)->first()
            ?? Course::find($courseId);

        if (! $course) {
            return response()->json(['message' => 'Cours introuvable.'], 404);
        }

        if (! $this->access->can($request->user(), $course, 'live_sessions')) {
            return response()->json([
                'message'        => 'Les séances en direct sont incluses dès la formule Standard.',
                'required_level' => 2,
            ], 403);
        }

        return response()->json([
            'data' => [
                'joinUrl'  => $session->zoom_join_url,
                'password' => $session->zoom_password,
            ],
        ]);
    }

    // ============================================================
    // GET /api/formateur/courses/{courseId}/live/host-url
    // 🔒 Formateur uniquement — récupère son start_url
    // ============================================================
    public function hostUrl(Request $request, string $courseId)
    {
        $session = LiveSession::where('course_id', $courseId)
            ->where('is_live', true)
            ->first();

        if (! $session || ! $session->hasZoom()) {
            return response()->json(['message' => 'Aucune session active.'], 404);
        }

        // Sécurité : seul le formateur propriétaire (ou admin) peut voir son start_url
        $user = $request->user();
        $isAdmin = method_exists($user, 'hasRole')
            ? $user->hasRole('admin')
            : ($user->role ?? null) === 'admin';

        if (! $isAdmin && (int) $session->instructor_id !== (int) $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return response()->json([
            'data' => [
                'startUrl' => $session->zoom_start_url,
            ],
        ]);
    }


        // POST /api/formateur/courses/{courseId}/live/schedule
    // POST /api/formateur/courses/{courseId}/live/schedule
public function scheduleMany(Request $request, string $courseId)
{
    $request->validate([
        'dates' => 'required|array|min:1',
        'dates.*' => 'required|date|after:now',
    ]);

    $course = Course::where('slug', $courseId)->first()
        ?? Course::find($courseId);

    if (! $course) {
        return response()->json(['message' => 'Cours introuvable.'], 404);
    }

    $created = [];

    foreach ($request->dates as $dateStr) {
        // Créer la réunion Zoom
        $meeting = $this->zoom->createMeeting(
            topic: "Séance live — {$course->title}",
            courseId: (string) $course->id,
        );

        if (! $meeting) {
            continue; // On passe à la date suivante
        }

        $schedule = \App\Models\LiveSchedule::create([
            'course_id'       => $course->id,
            'instructor_id'   => $request->user()->id,
            'scheduled_at'    => $dateStr,
            'zoom_meeting_id' => $meeting['id'],
            'zoom_join_url'   => $meeting['join_url'],
            'zoom_start_url'  => $meeting['start_url'],
            'zoom_password'   => $meeting['password'],
        ]);

        $created[] = $schedule;
    }

    return response()->json([
        'message' => count($created) . ' séances programmées avec succès.',
        'data'    => $created,
    ]);
}

public function getSchedules(string $courseId)
{
    $course = Course::where('slug', $courseId)->first()
        ?? Course::find($courseId);

    if (! $course) {
        return response()->json(['message' => 'Cours introuvable.'], 404);
    }

    $schedules = \App\Models\LiveSchedule::where('course_id', $course->id)
        ->orderBy('scheduled_at')
        ->get();

    return response()->json(['data' => $schedules]);
}


    // ============================================================
    // GET /api/formateur/live/upcoming-schedules
    // Renvoie les 5 prochaines séances programmées du formateur
    // ============================================================
    public function upcomingSchedules(Request $request)
    {
        $user = $request->user();

        // Récupérer tous les cours du formateur
        $courseIds = \App\Models\Course::where('instructor_id', $user->id)->pluck('id');

        $schedules = \App\Models\LiveSchedule::whereIn('course_id', $courseIds)
            ->where('scheduled_at', '>=', now())
            ->with('course:id,title,image') // ✅ On charge aussi l'image
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'courseTitle' => $s->course?->title ?? 'Cours',
                'courseImage' => $s->course?->image, // ✅ Ajout de l'image
                'scheduledAt' => $s->scheduled_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $schedules]);
    }
}