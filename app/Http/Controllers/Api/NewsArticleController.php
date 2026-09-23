<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;

class NewsArticleController extends Controller
{
    // GET /api/news-articles — PUBLIC
    public function index()
    {
        $articles = NewsArticle::where('published', true)
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->get();

        return response()->json([
            'data' => $articles->map(fn ($a) => $this->format($a))->values(),
        ]);
    }

    // GET /api/news-articles/{slug} — page détail
    public function show(string $slug)
    {
        $article = NewsArticle::where('slug', $slug)
            ->where('published', true)
            ->first();

        if (! $article) {
            return response()->json(['message' => 'Article introuvable.'], 404);
        }

        return response()->json(['data' => $this->format($article, detailed: true)]);
    }

    private function format(NewsArticle $a, bool $detailed = false): array
    {
        $date = $a->published_at ?? $a->created_at;

        return [
            'id'           => (string) $a->id,
            'slug'         => $a->slug,
            'category'     => $a->category,
            'title'        => $a->title,
            'excerpt'      => $a->excerpt,
            'image'        => $this->mediaUrl($a->image),
            'imageAlt'     => $a->image_alt ?? $a->title,
            'author'       => $a->author_name ?? 'Équipe KMAA',
            'authorRole'   => $a->author_role ?? 'Communication',
            'authorAvatar' => $a->author_avatar ?? 'https://images.unsplash.com/photo-1607346256330-dee7af15f7c5?w=200&q=80',
            'authorImage'  => $a->author_avatar ?? 'https://images.unsplash.com/photo-1607346256330-dee7af15f7c5?w=200&q=80',
            'date'         => $date?->translatedFormat('d F Y') ?? '',
            'dateIso'      => $date?->toDateString() ?? now()->toDateString(),
            'readTime'     => ($a->read_time ?: 4) . ' min',
            'featured'     => (bool) $a->featured,
            'route'        => '/actualites/' . $a->slug,
            'content'      => $detailed ? $this->paragraphs($a->content) : null,
        ];
    }

    /** Convertit le HTML du RichEditor en tableau de paragraphes. */
    private function paragraphs(?string $html): array
    {
        if (! $html) {
            return [];
        }

        $text = str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n\n", $html);

        return array_values(array_filter(
            array_map('trim', preg_split('/\n+/', strip_tags($text)) ?: [])
        ));
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) return null;
        if (str_starts_with($path, 'http') || str_starts_with($path, '/storage/')) return $path;
        return '/storage/' . $path;   // ✅ chemin relatif
    }
}