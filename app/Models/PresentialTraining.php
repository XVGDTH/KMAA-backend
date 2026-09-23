<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PresentialTraining extends Model
{
    // protected $fillable = [
    //     'title', 'description',
    //     'starts_at', 'ends_at', 'days_count', 'hours_count',
    //     'location', 'instructor_name', 'instructor_role',
    //     'expected_participants', 'registered_count',
    //     'slug', 'registration_token', 'registration_open',
    //     'certificate_type', 'created_by',
    // ];

    protected $fillable = [
    'title', 'description',
    'starts_at', 'ends_at', 'days_count', 'hours_count',
    'location', 'instructor_name', 'instructor_role',
    'max_participants', 'registered_count',   // ✅ CHANGÉ
    'slug', 'registration_token', 'registration_open',
    'certificate_type', 'created_by',
];

    // protected $casts = [
    //     'starts_at'         => 'date',
    //     'ends_at'           => 'date',
    //     'registration_open' => 'boolean',
    //     'days_count'        => 'integer',
    //     'hours_count'       => 'integer',
    //     'expected_participants' => 'integer',
    //     'registered_count'  => 'integer',
    // ];
    protected $casts = [
    'starts_at'         => 'date',
    'ends_at'           => 'date',
    'registration_open' => 'boolean',
    'days_count'        => 'integer',
    'hours_count'       => 'integer',
    'max_participants'  => 'integer',   // ✅ CHANGÉ
    'registered_count'  => 'integer',
];

    // ----------------------------------------------------------
    // RELATIONS
    // ----------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(PresentialParticipant::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    // ----------------------------------------------------------
    // HELPERS
    // ----------------------------------------------------------

    public function getRegistrationUrl(): string
    {
        return url("/p/{$this->registration_token}");
    }

    public function getPeriodLabel(): string
    {
        return sprintf(
            'du %s au %s',
            $this->starts_at->format('d/m/Y'),
            $this->ends_at->format('d/m/Y'),
        );
    }

    public function getCertificateTypeLabel(): string
    {
        return $this->certificate_type === 'certificate' ? 'Certificat' : 'Attestation';
    }

    // ----------------------------------------------------------
    // BOOT — slug + token auto
    // ----------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (PresentialTraining $training) {
            if (empty($training->slug)) {
                $training->slug = self::generateSlug($training->title);
            }
            if (empty($training->registration_token)) {
                $training->registration_token = Str::random(40);
            }
        });
    }

    private static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $i = 1;
        while (self::where('slug', $slug)->exists()) {
            $slug = Str::slug($title) . '-' . (++$i);
        }
        return $slug;
    }
}