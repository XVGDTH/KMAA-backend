<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveSchedule extends Model
{
    protected $fillable = [
        'course_id', 'instructor_id', 'scheduled_at',
        'zoom_meeting_id', 'zoom_join_url', 'zoom_start_url', 'zoom_password',
        'reminder_sent', 'replay_url',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    // ✅ AJOUTE CETTE MÉTHODE
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}