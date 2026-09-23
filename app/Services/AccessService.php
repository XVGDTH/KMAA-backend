<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;

class AccessService
{
    /**
     * Fonctionnalités de la plateforme et rang minimum requis.
     * UNE SEULE source de vérité pour tout le gating.
     */
    public const FEATURES = [
        'course_content'    => 1,   // vidéos, documents, quiz (socle)
        'certificate'       => 1,   // certificat de réussite
        'live_sessions'     => 2,   // séances en direct
        'assignment_review' => 2,   // soumission + correction des devoirs
        'coaching'          => 3,   // coaching individuel
        'team_management'   => 4,   // multi-apprenants + rapports
    ];

    /** Niveau d'accès = rang MAX parmi les paiements validés pour ce cours. */
    public function levelFor(User $user, Course $course): int
    {
        $payments = Payment::with('pricingPlan')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'paye')
            ->get();

        if ($payments->isEmpty()) {
            return 0;
        }

        // Un paiement sans formule = niveau 1 minimum (accès socle)
        return (int) $payments
            ->map(fn (Payment $p) => $p->pricingPlan?->level ?? 1)
            ->max();
    }

    public function can(User $user, Course $course, string $feature): bool
    {
        $required = self::FEATURES[$feature] ?? 999;

        return $this->levelFor($user, $course) >= $required;
    }
}