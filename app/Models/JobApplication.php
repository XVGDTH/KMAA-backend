<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'type','job_offer_id', 'full_name', 'email', 'phone',
        'message', 'cv_path', 'status',
    ];

    public function jobOffer()
    {
        return $this->belongsTo(JobOffer::class);
    }
}