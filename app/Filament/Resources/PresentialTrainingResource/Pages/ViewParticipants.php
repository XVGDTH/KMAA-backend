<?php

namespace App\Filament\Resources\PresentialTrainingResource\Pages;

use App\Filament\Resources\PresentialTrainingResource;
use App\Mail\PresentialCertificateMail;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Mail;

class ViewParticipants extends Page
{
    protected static string $resource = PresentialTrainingResource::class;
    protected static string $view = 'filament.resources.presential-training-resource.view-participants';

    public $record;

    public function mount($record): void
    {
        $this->record = PresentialTrainingResource::resolveRecordRouteBinding($record);
    }

    public function resendEmail($participantId): void
    {
        $participant = $this->record->participants()->findOrFail($participantId);

        if ($participant->revoked) {
            \Filament\Notifications\Notification::make()
                ->title('Ce participant a été retiré.')
                ->danger()
                ->send();
            return;
        }

        if (! $participant->certificate) {
            \Filament\Notifications\Notification::make()
                ->title('Aucun certificat à envoyer.')
                ->warning()
                ->send();
            return;
        }

        Mail::to($participant->email)
            ->send(new PresentialCertificateMail($participant, $participant->certificate));

        $participant->update(['certificate_sent_at' => now()]);

        \Filament\Notifications\Notification::make()
            ->title('Email renvoyé à ' . $participant->email)
            ->success()
            ->send();
    }

    public function revoke($participantId, $reason): void
    {
        if (empty(trim($reason))) {
            \Filament\Notifications\Notification::make()
                ->title('Le justificatif est obligatoire.')
                ->danger()
                ->send();
            return;
        }

        $participant = $this->record->participants()->findOrFail($participantId);

        $participant->update([
            'revoked'           => true,
            'revocation_reason' => $reason,
            'revoked_at'        => now(),
            'revoked_by'        => auth()->id(),
        ]);

        if ($participant->certificate) {
            $participant->certificate->update([
                'revoked'           => true,
                'revocation_reason' => $reason,
                'revoked_at'        => now(),
            ]);
        }

        \Filament\Notifications\Notification::make()
            ->title('Participant retiré.')
            ->success()
            ->send();
    }
}