<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    // protected $fillable = ['name', 'price','badge' 'price_note', 'description', 'featured', 'features'];
    protected $fillable = ['name', 'badge', 'price', 'price_note', 'description', 'featured', 'features'];

    protected function casts(): array
    {
        return ['featured' => 'boolean', 'features' => 'array'];
    }
}