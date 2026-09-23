<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;

class PricingPlanController extends Controller
{
    // GET /api/pricing-plans — PUBLIC
    public function index()
    {
        $plans = PricingPlan::orderBy('price')->get();

        return response()->json([
            'data' => $plans->map(fn ($p) => [
                'id'       => (string) $p->id,
                'name'     => $p->name,
                'price'    => (int) $p->price,
                'badge'    => $p->badge,
                'features' => $p->features ?? [],
                'featured' => (bool) $p->featured,
            ])->values(),
        ]);
    }
}