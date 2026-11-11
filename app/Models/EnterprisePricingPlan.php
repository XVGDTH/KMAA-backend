<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnterprisePricingPlan extends Model
{
    protected $fillable = [
        'slug', 'name', 'price_per_seat', 'min_seats', 'max_seats',
        'badge', 'description', 'features', 'featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features'     => 'array',
            'featured'     => 'boolean',
            'price_per_seat' => 'integer',
        ];
    }

    public function licenses()
    {
        return $this->hasMany(EnterpriseLicense::class, 'plan_id');
    }
}