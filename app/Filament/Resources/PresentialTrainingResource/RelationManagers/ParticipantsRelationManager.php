<?php

namespace App\Filament\Resources\PresentialTrainingResource\RelationManagers;

use App\Mail\PresentialCertificateMail;
use App\Models\PresentialParticipant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    protected static ?string $title = 'Participants inscrits';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nom complet')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required(),

            Forms\Components\TextInput::make('phone')
                ->label('Téléphone'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('registered_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\IconColumn::make('certificate_sent_at')
                    ->label('Email envoyé')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->certificate_sent_at !== null),

                Tables\Columns\IconColumn::make('revoked')
                    ->label('Retiré')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->actions([
                // Renvoyer l'email
                Tables\Actions\Action::make('resendEmail')
                    ->label('Renvoyer l\'email')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->visible(fn (PresentialParticipant $record) => ! $record->revoked)
                    ->action(function (PresentialParticipant $record) {
                        if (! $record->certificate) {
                            Notification::make()
                                ->title('Aucun certificat généré.')
                                ->warning()
                                ->send();
                            return;
                        }

                        Mail::to($record->email)
                            ->send(new PresentialCertificateMail($record, $record->certificate));

                        $record->update(['certificate_sent_at' => now()]);

                        Notification::make()
                            ->title('Email renvoyé à ' . $record->email)
                            ->success()
                            ->send();
                    }),

                // Retirer avec justificatif
                Tables\Actions\Action::make('revoke')
                    ->label('Retirer')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PresentialParticipant $record) => ! $record->revoked)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Justificatif (obligatoire)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (PresentialParticipant $record, array $data) {
                        $record->update([
                            'revoked'           => true,
                            'revocation_reason' => $data['reason'],
                            'revoked_at'        => now(),
                            'revoked_by'        => auth()->id(),
                        ]);

                        if ($record->certificate) {
                            $record->certificate->update([
                                'revoked'           => true,
                                'revocation_reason' => $data['reason'],
                                'revoked_at'        => now(),
                            ]);
                        }

                        Notification::make()
                            ->title('Participant retiré.')
                            ->success()
                            ->send();
                    }),

                // Télécharger le certificat
                Tables\Actions\Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (PresentialParticipant $record) =>
                        $record->certificate && ! $record->revoked
                    )
                    ->url(fn (PresentialParticipant $record) =>
                        route('certificates.download', $record->certificate->uuid)
                    )
                    ->openUrlInNewTab(),
            ]);
    }
}