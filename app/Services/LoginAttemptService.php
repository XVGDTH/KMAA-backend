<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoginAttemptService
{
    /**
     * Nombre max de tentatives avant blocage.
     */
    public const MAX_ATTEMPTS = 3;

    /**
     * Durée du blocage en minutes.
     */
    public const LOCK_DURATION_MINUTES = 15;

    /**
     * Enregistre une tentative échouée.
     */
    public function recordFailedAttempt(User $user, string $ip): void
    {
        $user->failed_login_attempts = ($user->failed_login_attempts ?? 0) + 1;
        $user->last_failed_login_at = now();
        $user->last_failed_login_ip = $ip;

        // Blocage si on atteint le max
        if ($user->failed_login_attempts >= self::MAX_ATTEMPTS) {
            $user->locked_until = now()->addMinutes(self::LOCK_DURATION_MINUTES);
            $user->lock_reason = 'Trop de tentatives échouées (sécurité)';

            Log::warning('🔒 Compte bloqué après tentatives multiples', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $ip,
                'attempts' => $user->failed_login_attempts,
                'locked_until' => $user->locked_until,
            ]);

            // Notification admin (à activer quand le service est prêt)
            // $this->notifyAdmin($user, $ip);
        }

        $user->save();
    }

    /**
     * Réinitialise les tentatives après connexion réussie.
     */
    public function clearAttempts(User $user): void
    {
        if ($user->failed_login_attempts > 0 || $user->locked_until) {
            $user->failed_login_attempts = 0;
            $user->locked_until = null;
            $user->lock_reason = null;
            $user->save();
        }
    }

    /**
     * Vérifie si l'utilisateur est bloqué.
     *
     * @return array{locked: bool, remaining_seconds: int, reason: ?string}
     */
    public function checkLocked(User $user): array
    {
        // Pas de blocage enregistré
        if (! $user->locked_until) {
            return ['locked' => false, 'remaining_seconds' => 0, 'reason' => null];
        }

        $lockedUntil = Carbon::parse($user->locked_until);

        // Blocage expiré → auto-déblocage
        if ($lockedUntil->isPast()) {
            $this->clearAttempts($user);

            return ['locked' => false, 'remaining_seconds' => 0, 'reason' => null];
        }

        // Toujours bloqué
        return [
            'locked' => true,
            'remaining_seconds' => (int) now()->diffInSeconds($lockedUntil),
            'reason' => $user->lock_reason,
        ];
    }

    /**
     * Débloque manuellement (appelé par un admin).
     */
    public function unlock(User $user): void
    {
        $user->failed_login_attempts = 0;
        $user->locked_until = null;
        $user->lock_reason = null;
        $user->save();

        Log::info('🔓 Compte débloqué manuellement', [
            'user_id' => $user->id,
            'email' => $user->email,
            'by_admin' => auth()->id(),
        ]);
    }
}