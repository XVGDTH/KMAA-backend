<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'name', 'status', 'unsubscribe_token', 'subscribed_at', 'unsubscribed_at'];

    protected function casts(): array
    {
        return [
            'subscribed_at'   => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Token généré automatiquement à la création
        static::creating(function ($subscriber) {
            $subscriber->unsubscribe_token ??= Str::random(40);
            $subscriber->subscribed_at ??= now();
        });
    }
}