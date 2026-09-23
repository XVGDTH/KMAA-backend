<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    protected $fillable = [
        'slug', 'title', 'excerpt', 'content', 'category',
        'image', 'image_alt', 'author_name', 'author_role', 'author_avatar',
        'read_time', 'featured', 'published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'featured'     => 'boolean',
            'published'    => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}