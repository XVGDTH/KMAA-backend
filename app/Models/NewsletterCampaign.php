<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterCampaign extends Model
{
    protected $fillable = [
        'subject', 'intro', 'content', 'status', 'sent_count', 'sent_at',
    ];
}