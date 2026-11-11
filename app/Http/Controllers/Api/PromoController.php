<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    /**
     * POST /api/promo/validate
     * Body: { code, subtotal, team_size }
     *
     * Retourne la réduction calculée côté serveur.
     * Public — pas besoin d'être authentifié (utile dans le devis).
     */
    public function validate(Request $request)
    {
        $data = $request->validate([
            'code'      => 'required|string|max:50',
            'subtotal'  => 'required|integer|min:0',
            'team_size' => 'required|integer|min:1',
        ]);

        $promo = PromoCode::active()
            ->where('code', strtoupper(trim($data['code'])))
            ->first();

        if (! $promo) {
            return response()->json([
                'valid'   => false,
                'message' => 'Code promo invalide.',
            ], 422);
        }

        $error = $promo->validationError((int) $data['subtotal'], (int) $data['team_size']);

        if ($error !== null) {
            return response()->json([
                'valid'   => false,
                'message' => $error,
            ], 422);
        }

        $discount = $promo->computeDiscount((int) $data['subtotal']);

        return response()->json([
            'valid'    => true,
            'code'     => $promo->code,
            'type'     => $promo->type,
            'value'    => $promo->value,
            'label'    => $promo->label,
            'discount' => $discount,
        ]);
    }
}