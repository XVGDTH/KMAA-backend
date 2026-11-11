<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZoomService
{
    /**
     * Récupère un access token OAuth (mis en cache 55 min).
     */
    private function getAccessToken(): ?string
    {
        return Cache::remember('zoom_access_token', 3300, function () {
            $accountId    = config('services.zoom.account_id');
            $clientId     = config('services.zoom.client_id');
            $clientSecret = config('services.zoom.client_secret');

            if (! $accountId || ! $clientId || ! $clientSecret) {
                Log::error('Zoom : credentials manquants dans .env');
                return null;
            }

            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post(config('services.zoom.oauth_url'), [
                    'grant_type' => 'account_credentials',
                    'account_id' => $accountId,
                ]);

            if (! $response->successful()) {
                Log::error('Zoom OAuth échec', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json('access_token');
        });
    }

    /**
     * Crée une réunion Zoom pour un cours.
     *
     * @return array{id: string, start_url: string, join_url: string, password: string}|null
     */
    public function createMeeting(string $topic, string $courseId): ?array
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return null;
        }

        $payload = [
            'topic'      => $topic,
            'type'       => 1,                 // 1 = réunion instantanée
            'start_time' => now()->toIso8601String(),
            'duration'   => 120,               // 2h par défaut
            'timezone'   => 'Africa/Ouagadougou',
            'settings'   => [
                'host_video'        => true,
                'auto_recording' => 'cloud', // ✅ Enregistrement automatique sur le cloud Zoom
                'participant_video' => true,
                'join_before_host'  => false,
                'auto_recording' => 'cloud',
                'mute_upon_entry'   => true,
                'waiting_room'      => false,
                'auto_recording'    => 'none',
            ],
        ];

        $response = Http::withToken($token)
            ->post(config('services.zoom.api_base') . '/users/me/meetings', $payload);

        if (! $response->successful()) {
            Log::error('Zoom createMeeting échec', [
                'course_id' => $courseId,
                'status'    => $response->status(),
                'body'      => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();

        return [
            'id'        => (string) $data['id'],
            'start_url' => $data['start_url'],
            'join_url'  => $data['join_url'],
            'password'  => $data['password'] ?? '',
        ];
    }

    /**
     * Termine une réunion Zoom (met fin pour tous les participants).
     */
    public function endMeeting(string $meetingId): bool
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return false;
        }

        $response = Http::withToken($token)
            ->put(config('services.zoom.api_base') . "/meetings/{$meetingId}/status", [
                'action' => 'end',
            ]);

        // Zoom renvoie 204 (No Content) en cas de succès
        return $response->successful() || $response->status() === 204;
    }
}