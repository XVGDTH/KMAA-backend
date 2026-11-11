<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZoomWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Zoom envoie un "event" dans le payload
        $event = $request->input('event');
        
        Log::info('Zoom Webhook reçu', ['event' => $event]);

        // ✅ Événement : enregistrement terminé
        if ($event === 'recording.completed') {
            $payload = $request->input('payload.object', []);
            $meetingId = $payload['id'] ?? null;
            
            // Chercher l'URL du replay (share_url)
            $recordingFiles = $payload['recording_files'] ?? [];
            $replayUrl = null;
            
            foreach ($recordingFiles as $file) {
                // On privilégie la vidéo MP4
                if (($file['file_type'] ?? '') === 'MP4') {
                    $replayUrl = $file['play_url'] ?? $file['download_url'] ?? null;
                    break;
                }
            }
            
            // Si pas de MP4, on prend le share_url global
            if (!$replayUrl) {
                $replayUrl = $payload['share_url'] ?? null;
            }

            if ($meetingId && $replayUrl) {
                // Mettre à jour la session correspondante
                LiveSchedule::where('zoom_meeting_id', $meetingId)
                    ->update(['replay_url' => $replayUrl]);
                    
                Log::info('Replay enregistré', ['meeting_id' => $meetingId, 'url' => $replayUrl]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}