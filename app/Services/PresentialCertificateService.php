<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\PresentialParticipant;
use Illuminate\Support\Str;

class PresentialCertificateService
{
    /**
     * Génère (ou retourne) le certificat d'un participant présentiel.
     */
    public static function generate(PresentialParticipant $participant): Certificate
    {
        // Idempotent : si déjà généré, on retourne l'existant
        if ($participant->certificate_id) {
            return $participant->certificate;
        }

        $training = $participant->training;

        $certificate = Certificate::create([
            'enrollment_id'          => null,
            'presential_training_id' => $training->id,
            'uuid'                   => (string) Str::uuid(),
            'reference'              => Certificate::generateReference(),
            'student_name'           => $participant->name,
            'course_title'           => $training->title,
            'type'                   => $training->certificate_type ?? 'attestation',
            'duration_hours'         => $training->hours_count ?? 8,
            'issued_at'              => now(),
        ]);

        $participant->update(['certificate_id' => $certificate->id]);

        return $certificate;
    }
}