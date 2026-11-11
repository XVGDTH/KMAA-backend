<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'completed_at', 'enrolled_at', 'last_accessed_at', 'certificate_issued', 
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'completed_at'       => 'datetime', 
            'certificate_issued' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function sequenceProgress()
    {
        return $this->hasMany(SequenceProgress::class);
    }

public function progressPercent(): int
{
    $courseId = $this->course_id;

    // 1. Récupérer toutes les séquences du cours
    $sequences = \App\Models\Sequence::whereHas('chapter', fn($q) => $q->where('course_id', $courseId))
        ->orWhereHas('module', fn($q) => $q->where('course_id', $courseId))
        ->orWhere('course_id', $courseId)
        ->get();

    if ($sequences->isEmpty()) return 0;

    // 2. Récupérer les progressions
    $progressions = $this->sequenceProgress()->get()->keyBy('sequence_id');

    // 3. Récupérer les soumissions
    $submissions = \App\Models\AssignmentSubmission::where('user_id', $this->user_id)
        ->where('course_id', $courseId)
        ->get()
        ->keyBy('sequence_id');

    $completedCount = 0;

    foreach ($sequences as $seq) {
        $prog = $progressions->get($seq->id);
        $isComplete = false;

        if (in_array($seq->type, ['video', 'document'])) {
            $isComplete = $prog && $prog->completed;
        } elseif ($seq->type === 'quiz') {
            $isComplete = $prog && $prog->completed && $prog->passed;
        } elseif (in_array($seq->type, ['devoir', 'td', 'tp', 'cas_pratique'])) {
            $sub = $submissions->get($seq->id);
            $isComplete = $prog && $prog->completed && $sub && $sub->status === 'corrige';
        }

        if ($isComplete) $completedCount++;
    }

    return (int) round(($completedCount / $sequences->count()) * 100);
}
}