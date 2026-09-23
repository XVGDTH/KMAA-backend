<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'slug', 'title', 'excerpt', 'content', 'category', 'kind',
        'image', 'image_alt', 'author_id', 'author_name', 'author_role',
        'author_avatar', 'read_time', 'tags',
        'featured', 'popular', 'level', 'audience',
        'published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'featured'     => 'boolean',
            'published'    => 'boolean',
            'popular'      => 'boolean',
            'published_at' => 'datetime',
            'tags'         => 'array',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}