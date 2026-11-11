<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'label',
        'min_subtotal', 'min_team_size', 'max_uses', 'used_count',
        'starts_at', 'ends_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'         => 'integer',
            'min_subtotal'  => 'integer',
            'min_team_size' => 'integer',
            'max_uses'      => 'integer',
            'used_count'    => 'integer',
            'is_active'     => 'boolean',
            'starts_at'     => 'datetime',
            'ends_at'       => 'datetime',
        ];
    }

    // ============================================================
    // VALIDATION
    // ============================================================

    /**
     * Vérifie si le code est utilisable pour un panier donné.
     * Retourne un message d'erreur si invalide, null si OK.
     */
    public function validationError(int $subtotal, int $teamSize): ?string
    {
        if (! $this->is_active) {
            return 'Ce code promo n\'est plus actif.';
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return 'Ce code promo n\'est pas encore valide.';
        }

        if ($this->ends_at && now()->gt($this->ends_at)) {
            return 'Ce code promo a expiré.';
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return 'Ce code promo a atteint sa limite d\'utilisation.';
        }

        if ($subtotal < $this->min_subtotal) {
            return 'Le montant minimum pour ce code est de '
                . number_format($this->min_subtotal, 0, ',', ' ') . ' FCFA.';
        }

        if ($teamSize < $this->min_team_size) {
            return "Ce code nécessite au moins {$this->min_team_size} apprenant(s).";
        }

        return null;
    }

    /**
     * Calcule la réduction (en FCFA) pour un sous-total donné.
     */
    public function computeDiscount(int $subtotal): int
    {
        if ($this->type === 'percent') {
            return (int) round($subtotal * $this->value / 100);
        }
        return (int) min($this->value, $subtotal);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}