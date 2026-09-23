<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    // ========================================================
    // RELATIONS
    // ========================================================

    /** Le cours auquel appartient ce module. */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Les chapitres de ce module (ordonnés par position).
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    /**
     * Le quiz de fin de module (séquence type='quiz' rattachée au module).
     * Peut ne pas exister — c'est au formateur de décider.
     */
    public function moduleQuiz(): HasMany
    {
        return $this->hasMany(Sequence::class)
            ->where('type', 'quiz');
    }
}