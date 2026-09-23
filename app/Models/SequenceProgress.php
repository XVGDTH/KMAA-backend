<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SequenceProgress extends Model
{
    protected $table = 'sequence_progress';

    protected $fillable = ['enrollment_id', 'sequence_id', 'completed', 'completed_at'];

    protected function casts(): array
    {
        return ['completed' => 'boolean', 'completed_at' => 'datetime'];
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function sequence()
    {
        return $this->belongsTo(Sequence::class);
    }
}