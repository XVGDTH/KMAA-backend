<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\TeamSetting;

class TeamController extends Controller
{
    // GET /api/team — PUBLIC (un seul appel : membres + textes)
    public function index()
    {
        $members = TeamMember::orderByDesc('featured')
            ->orderBy('position')
            ->get();

        $settings = TeamSetting::first();

        return response()->json([
            'data' => [
                'members'  => $members->map(fn ($m) => $this->formatMember($m))->values(),
                'settings' => $settings ? $this->formatSettings($settings) : null,
            ],
        ]);
    }

    private function formatMember(TeamMember $m): array
    {
        return [
            'id'         => (string) $m->id,
            'name'       => $m->name,
            'role'       => $m->role,
            'department' => $m->department,
            'bio'        => $m->bio ?? '',
            'image'      => $this->mediaUrl($m->image),
            'imageAlt'   => $m->image_alt ?? $m->name,
            'linkedin'   => $m->linkedin,
            'featured'   => (bool) $m->featured,
        ];
    }

    private function formatSettings(TeamSetting $s): array
    {
        return [
            'heroEyebrow'     => $s->hero_eyebrow,
            'heroTitle'       => $s->hero_title,
            'heroTitleAccent' => $s->hero_title_accent,
            'heroLead'        => $s->hero_lead,
            'stats'           => $s->stats ?? [],
            'cultureTitle'    => $s->culture_title,
            'cultureText'     => $s->culture_text,
            'cultureValues'   => $s->culture_values ?? [],
            'ctaTitle'        => $s->cta_title,
            'ctaText'         => $s->cta_text,
        ];
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) return null;
        if (str_starts_with($path, 'http') || str_starts_with($path, '/storage/')) return $path;
        return '/storage/' . $path;   // ✅ chemin relatif
    }
}