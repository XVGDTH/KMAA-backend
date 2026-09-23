<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;

class JobOfferController extends Controller
{
    // GET /api/job-offers — PUBLIC
    public function index()
    {
        $jobs = JobOffer::where('published', true)
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->get();

        return response()->json([
            'data' => $jobs->map(fn ($j) => $this->format($j))->values(),
        ]);
    }

    // GET /api/job-offers/{id} — page détail
    public function show(int $id)
    {
        $job = JobOffer::where('published', true)->find($id);

        if (! $job) {
            return response()->json(['message' => 'Offre introuvable.'], 404);
        }

        return response()->json(['data' => $this->format($job, detailed: true)]);
    }

    private function format(JobOffer $j, bool $detailed = false): array
    {
        return [
            'id'          => (string) $j->id,
            'title'       => $j->title,
            'type'        => $j->type,
            'typeLabel'   => $this->typeLabel($j->type),
            'location'    => $j->location ?? '',
            'department'  => $j->department ?? '',
            'excerpt'     => $j->excerpt,
            'tags'        => $j->tags ?? [],
            'image'       => $this->mediaUrl($j->image),
            'imageAlt'    => $j->image_alt ?? $j->title,
            'featured'    => (bool) $j->featured,
            'description' => $detailed ? $this->paragraphs($j->description) : null,
            'missions'    => $detailed ? ($j->missions ?? []) : null,
            'profile'     => $detailed ? ($j->profile ?? []) : null,
        ];
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'cdi'        => 'CDI',
            'cdd'        => 'CDD',
            'stage'      => 'Stage',
            'freelance'  => 'Freelance',
            'partenaire' => 'Partenariat',
            default      => ucfirst($type),
        };
    }

    /** RichEditor HTML → tableau de paragraphes. */
    private function paragraphs(?string $html): array
    {
        if (! $html) return [];

        $text = str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n\n", $html);

        return array_values(array_filter(
            array_map('trim', preg_split('/\n+/', strip_tags($text)) ?: [])
        ));
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) return null;
        if (str_starts_with($path, 'http') || str_starts_with($path, '/storage/')) return $path;
        return '/storage/' . $path;
    }
}