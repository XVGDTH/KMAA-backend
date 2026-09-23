<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobOffer extends Model
{
    protected $fillable = [
        'title', 'type', 'location', 'department', 'excerpt',
        'description', 'missions', 'profile', 'tags',
        'image', 'image_alt', 'featured', 'published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'missions' => 'array',
            'profile'  => 'array',
            'tags'     => 'array',
            'featured' => 'boolean',
            'published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}