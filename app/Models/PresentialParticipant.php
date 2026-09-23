<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresentialParticipant extends Model
{
    protected $fillable = [
        'presential_training_id',
        'name', 'email', 'phone',
        'registered_at', 'certificate_sent_at',
        'certificate_id',
        'revoked', 'revocation_reason', 'revoked_at', 'revoked_by',
    ];

    protected $casts = [
        'registered_at'       => 'datetime',
        'certificate_sent_at' => 'datetime',
        'revoked'             => 'boolean',
        'revoked_at'          => 'datetime',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(PresentialTraining::class, 'presential_training_id');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}