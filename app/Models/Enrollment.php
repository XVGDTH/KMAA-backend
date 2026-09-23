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
        $total = $this->course->chapters->flatMap->sequences->count();
        if ($total === 0) return 0;

        $done = $this->sequenceProgress()->where('completed', true)->count();

        return (int) round(($done / $total) * 100);
    }
}