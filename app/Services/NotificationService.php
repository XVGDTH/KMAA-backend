<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PlatformNotification;

class NotificationService
{
    /**
     * Envoie une notification plateforme (+ email) à un ou plusieurs users.
     */
    public static function send(
        string $kind,
        string $title,
        string $message,
        $recipients,                 // User | Collection de Users
        ?string $courseSlug = null,
        ?string $actionUrl = null,
    ): void {
        if ($recipients instanceof User) {
            $recipients = collect([$recipients]);
        }

        $recipients->each(function (User $user) use ($kind, $title, $message, $courseSlug, $actionUrl) {
            if (! $user) return;
            $user->notify(new PlatformNotification($kind, $title, $message, $courseSlug, $actionUrl));
        });
    }
}