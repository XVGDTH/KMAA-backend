<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sequence extends Model
{
    protected $fillable = [
        'chapter_id',
        'module_id',          // 🆕 quiz de fin de module
        'course_id',          // 🆕 examen final
        'title',
        'type',               // 'video' | 'document' | 'tp' | 'td' | 'cas_pratique' | 'quiz'
        'passing_score',      // 🆕 seuil de réussite (85 par défaut si bloquant)
        'is_blocking',        // 🆕 quiz bloquant
        'is_final',           // 🆕 examen final
        'duration',
        'video_url',
        'document_url',
        'quiz_questions',
        'instructions',
        'deadline',           // 🆕 cas pratique
        'allow_file_upload',  // 🆕 cas pratique
        'position',
    ];

    protected function casts(): array
    {
        return [
            'quiz_questions'    => 'array',
            'position'          => 'integer',
            'passing_score'     => 'integer',
            'is_blocking'       => 'boolean',
            'is_final'          => 'boolean',
            'allow_file_upload' => 'boolean',
            'deadline'          => 'datetime',
        ];
    }

    // ========================================================
    // RELATIONS
    // ========================================================

    /** Le chapitre auquel appartient la séquence (null si quiz de module ou examen final). */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Le module auquel appartient la séquence.
     * Utilisé UNIQUEMENT pour les quiz de fin de module
     * (type='quiz', chapter_id=null, module_id rempli).
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Le cours auquel appartient la séquence.
     * Utilisé UNIQUEMENT pour l'examen final
     * (type='quiz', is_final=true, course_id rempli).
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(SequenceProgress::class);
    }

    // ========================================================
    // HELPERS
    // ========================================================

    /** Est-ce un quiz (de chapitre, de module ou final) ? */
    public function isQuiz(): bool
    {
        return $this->type === 'quiz';
    }

    /** Est-ce un cas pratique ? */
    public function isCasPratique(): bool
    {
        return $this->type === 'cas_pratique';
    }

    /** Est-ce l'examen final ? */
    public function isFinalExam(): bool
    {
        return $this->type === 'quiz' && $this->is_final === true;
    }

    /** Est-ce un quiz de fin de module ? */
    public function isModuleQuiz(): bool
    {
        return $this->type === 'quiz'
            && $this->module_id !== null
            && $this->chapter_id === null;
    }

    /** Est-ce un quiz de fin de chapitre ? */
    public function isChapterQuiz(): bool
    {
        return $this->type === 'quiz' && $this->chapter_id !== null;
    }
}