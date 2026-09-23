<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $fillable = [
        'name', 'role', 'department', 'bio',
        'image', 'image_alt', 'linkedin',
        'featured', 'position',
    ];

    protected function casts(): array
    {
        return ['featured' => 'boolean'];
    }
}