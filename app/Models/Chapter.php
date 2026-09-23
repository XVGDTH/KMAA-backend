<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $fillable = [
        'course_id',
        'module_id',      // 🆕 rattachement au module
        'title',
        'week_number',    // 🆕 label "Semaine 1" (optionnel)
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position'    => 'integer',
            'week_number' => 'integer',
        ];
    }

    // ========================================================
    // RELATIONS
    // ========================================================

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Le module auquel appartient ce chapitre.
     * Peut être null pour les anciens chapitres orphelins.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Les séquences du chapitre (vidéos, documents, quiz, cas pratiques…).
     */
    public function sequences(): HasMany
    {
        return $this->hasMany(Sequence::class)->orderBy('position');
    }

    /**
     * Le quiz de fin de chapitre (optionnel — le formateur décide).
     */
    public function chapterQuiz(): HasMany
    {
        return $this->hasMany(Sequence::class)
            ->where('type', 'quiz');
    }
}