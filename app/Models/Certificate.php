<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'enrollment_id',
        'presential_training_id',   // 🆕
        'uuid',
        'reference',
        'student_name',             // 🆕 snapshot
        'course_title',             // 🆕 snapshot
        'type',
        'duration_hours',
        'issued_at',
        'revoked',                  // 🆕
        'revocation_reason',        // 🆕
        'revoked_at',               // 🆕
    ];

    protected $casts = [
        'issued_at'  => 'datetime',
        'revoked'    => 'boolean',
        'revoked_at' => 'datetime',
    ];

    // ----------------------------------------------------------
    // RELATIONS
    // ----------------------------------------------------------

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function presentialTraining(): BelongsTo
    {
        return $this->belongsTo(PresentialTraining::class);
    }

    // ----------------------------------------------------------
    // HELPERS
    // ----------------------------------------------------------

    public function isPresential(): bool
    {
        return $this->presential_training_id !== null;
    }

    public function getStudentName(): string
    {
        return $this->student_name
            ?? $this->enrollment?->user?->name
            ?? '—';
    }

    public function getCourseTitle(): string
    {
        return $this->course_title
            ?? $this->enrollment?->course?->title
            ?? '—';
    }

    // ----------------------------------------------------------
    // GÉNÉRATION DE RÉFÉRENCE (inchangé)
    // ----------------------------------------------------------

    public static function generateReference(): string
    {
        $prefix = 'KMAA-AC';
        $length = 20 - strlen($prefix);
        $charset = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789-_';

        do {
            $suffix = '';
            for ($i = 0; $i < $length; $i++) {
                $suffix .= $charset[random_int(0, strlen($charset) - 1)];
            }
            $reference = $prefix . $suffix;
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }
}