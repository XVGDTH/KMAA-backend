<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;

class BlogPostController extends Controller
{
    // GET /api/blog-posts — PUBLIC
    public function index()
    {
        $posts = BlogPost::with('author:id,name')
            ->where('published', true)
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $posts->map(fn ($post) => $this->format($post))->values(),
        ]);
    }

    private function format(BlogPost $post): array
    {
        return [
            'id'           => (string) $post->id,
            'slug'         => $post->slug,
            'title'        => $post->title,
            'excerpt'      => $post->excerpt,
            'content'      => $post->content,
            'category'     => $post->category,
            'kind'         => $post->kind ?? 'article',
            'image'        => $this->mediaUrl($post->image),
            'imageAlt'     => $post->image_alt ?? $post->title,
            'author'       => $post->author_name ?? $post->author?->name ?? 'Équipe KMAA',
            'authorRole'   => $post->author_role ?? 'KMAA Academy',
            'authorAvatar' => $post->author_avatar ?? 'https://i.pravatar.cc/100?img=8',
            'date'         => $post->published_at
                                ? $post->published_at->translatedFormat('d F Y')
                                : $post->created_at->translatedFormat('d F Y'),
            'dateIso'      => ($post->published_at ?? $post->created_at)->toDateString(),
            'readTime'     => ($post->read_time ?: 5) . ' min',
            'readMinutes'  => (int) ($post->read_time ?: 5),
            'featured'     => (bool) $post->featured,
            'popular'      => (bool) $post->popular,
            'route'        => '/blog/' . $post->slug,
            'tags'         => $post->tags ?? [],
            'level'        => $post->level,
            'audience'     => $post->audience,
        ];
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http') || str_starts_with($path, '/storage/')) {
            return $path;
        }

        return '/storage/' . $path;   // ✅ chemin relatif (règle établie)
    }
}