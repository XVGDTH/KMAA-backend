<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{

//     protected $fillable = [
//     'slug', 'subject', 'category', 'title', 'description', 'long_description',
//     'level', 'diploma', 'duration_full', 'duration_alternance',
//     'image', 'image_alt', 'popular', 'badge', 'is_free',
//     'has_certificate', 'instructor_id', 'promotion_label',
//     'price', 'promo_price', 'promo_code', 'provider', 'language', 'start_date',
// ];
protected $fillable = [
    'slug', 'type', 'subject', 'category', 'title', 'description', 'long_description',
    'level', 'diploma', 'duration_full', 'duration_alternance',
    'image', 'image_alt', 'popular', 'badge', 'is_free',
    'has_certificate', 'instructor_id', 'promotion_label',
    'price', 'promo_price', 'promo_code', 'provider', 'language', 'start_date',
    'delivery_mode', 'hours_per_week', 'modules_count', 'enterprise_base_price',
];
    protected static function booted(): void
    {
        static::saving(function (Course $course) {
            // Toujours true désormais : le champ "diploma" n'offre plus que
            // deux choix (certificat ou attestation), donc un document est
            // TOUJOURS délivré à la réussite.
            $course->has_certificate = in_array($course->diploma, ['certificate', 'attestation'], true);
        });
    }

    // protected function casts(): array
    // {
    //     return [
    //         'popular'         => 'boolean',
    //         // 'is_free'         => 'boolean',
    //         'has_certificate' => 'boolean',
    //                 'popular'         => 'boolean',
    //     'is_free'         => 'boolean',
    //     // 'has_certificate' => 'boolean',
    //     'price'           => 'integer',
    //     'promo_price'     => 'integer',
    //     ];
    // }
    protected function casts(): array
{
    return [
        'popular'                => 'boolean',
        'is_free'                => 'boolean',
        'has_certificate'        => 'boolean',
        'price'                  => 'integer',
        'promo_price'            => 'integer',
        'modules_count'          => 'integer',
        'enterprise_base_price'  => 'integer',
    ];
}
// ========================================================
// SCOPES
// ========================================================

public function scopeIndividual($query)
{
    return $query->where('type', 'individual');
}

public function scopeEnterprise($query)
{
    return $query->where('type', 'enterprise');
}

// ========================================================
// RELATIONS
// ========================================================

public function enterpriseLicenses()
{
    return $this->hasMany(EnterpriseLicense::class);
}

    // ========================================================
    // RELATIONS
    // ========================================================

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    /**
     * Les modules du cours (Analyse financière, Évaluation de société…).
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('position');
    }

    /**
     * L'examen final du cours (séquence type='quiz', is_final=true, course_id rempli).
     * Peut ne pas exister — le formateur décide.
     */
    public function finalExam(): HasMany
    {
        return $this->hasMany(Sequence::class)
            ->where('is_final', true)
            ->whereNull('module_id')
            ->whereNull('chapter_id');
    }


}
