<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamSetting extends Model
{
    protected $fillable = [
        'hero_eyebrow', 'hero_title', 'hero_title_accent', 'hero_lead',
        'stats', 'culture_title', 'culture_text', 'culture_values',
        'cta_title', 'cta_text',
    ];

    protected function casts(): array
    {
        return ['stats' => 'array', 'culture_values' => 'array'];
    }
}