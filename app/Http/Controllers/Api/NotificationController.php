<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications — mes notifications (non-lues d'abord)
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()                 // ✅ méthode native de Notifiable
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return response()->json([
            'data' => $notifications->map(fn ($n) => [
                'id'         => $n->id,
                'kind'       => $n->data['kind'] ?? 'info',
                'title'      => $n->data['title'] ?? '',
                'message'    => $n->data['message'] ?? '',
                'courseSlug' => $n->data['courseSlug'] ?? null,
                'read'       => $n->read_at !== null,
                'createdAt'  => $n->created_at->diffForHumans(),
            ]),
            'unreadCount' => $request->user()->unreadNotifications->count(),   // ✅ natif
        ]);
    }

    // POST /api/notifications/read — marquer UNE notification comme lue
    public function markRead(Request $request)
    {
        $request->validate(['id' => ['required', 'string']]);

        $notification = $request->user()
            ->notifications()
            ->where('id', $request->input('id'))
            ->first();

        if ($notification) {
            $notification->markAsRead();   // ✅ natif
        }

        return response()->json(['message' => 'Notification lue.']);
    }

    // POST /api/notifications/read-all — tout marquer comme lu
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();   // ✅ natif

        return response()->json(['message' => 'Toutes les notifications ont été lues.']);
    }
}