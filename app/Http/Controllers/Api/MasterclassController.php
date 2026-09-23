<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masterclass;

class MasterclassController extends Controller
{
    // GET /api/masterclass — PUBLIC (la masterclass active)
    public function show()
    {
        $mc = Masterclass::where('published', true)
            ->orderByDesc('created_at')
            ->first();

        if (! $mc) {
            return response()->json(['message' => 'Aucune masterclass disponible.'], 404);
        }

        return response()->json([
            'data' => [
                'masterclass' => [
                    'category'    => $mc->category,
                    'title'       => $mc->title,
                    'subtitle'    => $mc->subtitle,
                    'description' => $mc->description,
                    'date'        => $mc->date_label,
                    'time'        => $mc->time_label,
                    'timezone'    => $mc->timezone,
                    'participants'=> $mc->participants,
                    'capacity'    => $mc->capacity,
                    'language'    => $mc->language,
                ],
                'status'     => $mc->status,
                'youtube_id' => $mc->youtube_id,
                'speaker' => [
                    'name'          => $mc->speaker_name,
                    'role'          => $mc->speaker_role,
                    'organization'  => $mc->speaker_organization,
                    'bio'           => $mc->speaker_bio,
                    'image'         => $this->mediaUrl($mc->speaker_image),
                    'stats'         => $mc->speaker_stats ?? [],
                ],
                'sessions'  => $mc->sessions ?? [],
                'videos'    => $mc->videos ?? [],
                'resources' => $mc->resources ?? [],
                'quiz'      => $mc->quiz ?? [],
            ],
        ]);
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) return null;
        if (str_starts_with($path, 'http') || str_starts_with($path, '/storage/')) return $path;
        return '/storage/' . $path;
    }
}