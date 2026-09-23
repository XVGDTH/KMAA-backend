<?php

namespace App\Filament\Resources\AssignmentSubmissionResource\Pages;

use App\Filament\Resources\AssignmentSubmissionResource;
use App\Services\NotificationService;
use Filament\Resources\Pages\EditRecord;

class EditAssignmentSubmissions extends EditRecord
{
    protected static string $resource = AssignmentSubmissionResource::class;

    /**
     * ✅ NOTIFICATION : quand l'admin/formateur corrige via Filament,
     * l'apprenant reçoit la notification (DB + email).
     */
    protected function afterSave(): void
    {
        $submission = $this->record->fresh();

        if (
            $submission->status === 'corrige'
            && $submission->grade !== null
        ) {
            NotificationService::send(
                'devoir_corrige',
                'Ton devoir a été corrigé : ' . $submission->grade . '/20',
                'Séquence « ' . $submission->sequence?->title . ' » — Feedback : ' .
                    ($submission->instructor_feedback ?? 'Consulte ta correction.'),
                $submission->user,
                $submission->course->slug,
            );
        }
    }
}