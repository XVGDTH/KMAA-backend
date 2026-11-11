<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    protected $fillable = [
        'course_id',
        'instructor_id',
        'is_live',
        'room_name',
        'zoom_meeting_id',
        'zoom_start_url',
        'zoom_join_url',
        'zoom_password',
        'started_at',
        'scheduled_at',
    ];

    protected $casts = [
        'is_live'    => 'boolean',
        'started_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    /**
     * Helper : la session utilise-t-elle Zoom ?
     */
    public function hasZoom(): bool
    {
        return ! empty($this->zoom_meeting_id);
    }

    /**
     * Helper : URL à envoyer au formateur (hôte).
     */
    public function getHostUrl(): ?string
    {
        return $this->zoom_start_url;
    }

    /**
     * Helper : URL à envoyer aux apprenants.
     */
    public function getParticipantUrl(): ?string
    {
        return $this->zoom_join_url;
    }
}