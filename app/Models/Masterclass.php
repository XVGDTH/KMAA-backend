<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Masterclass extends Model
{
    protected $fillable = [
        'category', 'title', 'subtitle', 'description',
        'date_label', 'time_label', 'timezone',
        'participants', 'capacity', 'language', 'status', 'youtube_id',
        'speaker_name', 'speaker_role', 'speaker_organization',
        'speaker_bio', 'speaker_image', 'speaker_stats',
        'sessions', 'videos', 'resources', 'quiz',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'speaker_stats' => 'array',
            'sessions'      => 'array',
            'videos'        => 'array',
            'resources'     => 'array',
            'quiz'          => 'array',
            'published'     => 'boolean',
        ];
    }
}