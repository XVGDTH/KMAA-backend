<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'course_id',
        'max_uses',
        'used_count',
        'active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Génère un code unique : KMAA + 9 caractères aléatoires
     * Exemple : KMAA7X9K2M4P1
     */
    public static function generateUniqueCode(): string
    {
        do {
            $random = strtoupper(Str::random(9)); // lettres + chiffres
            $code = 'KMAA' . $random;
        } while (self::where('code', $code)->exists());

        return $code;
    }

    protected static function booted(): void
    {
        static::creating(function (Voucher $voucher) {
            if (empty($voucher->code)) {
                $voucher->code = self::generateUniqueCode();
            }
        });
    }
}