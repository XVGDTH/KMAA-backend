<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = '🎓 Formations';
    protected static ?string $navigationLabel = 'Certificats & Attestations';
    protected static ?string $modelLabel = 'Certificat';
    protected static ?string $pluralModelLabel = 'Certificats & Attestations';

    // Pas de formulaire (les certificats sont générés automatiquement)
    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('student_name')
                    ->label('Apprenant')
                    ->searchable()
                    ->weight('bold')
                    ->default('—'),

                Tables\Columns\TextColumn::make('course_title')
                    ->label('Formation')
                    ->searchable()
                    ->limit(40)
                    ->default('—'),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state === 'certificate' ? '🏆 Certificat' : '📜 Attestation')
                    ->colors([
                        'warning' => 'certificate',
                        'info'    => 'attestation',
                    ]),

                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->getStateUsing(fn (Certificate $record) =>
                        $record->presential_training_id ? '📍 Présentiel' : '💻 En ligne'
                    )
                    ->badge()
                    ->color(fn (Certificate $record) =>
                        $record->presential_training_id ? 'success' : 'primary'
                    ),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Délivré le')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('revoked')
                    ->label('Révoqué')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'certificate' => '🏆 Certificat',
                        'attestation' => '📜 Attestation',
                    ]),

                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'presential' => '📍 Présentiel',
                        'online'     => '💻 En ligne',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === 'presential') {
                            $query->whereNotNull('presential_training_id');
                        } elseif (($data['value'] ?? null) === 'online') {
                            $query->whereNotNull('enrollment_id');
                        }
                    }),

                Tables\Filters\TernaryFilter::make('revoked')
                    ->label('Révoqué'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Certificate $record) =>
                        route('certificates.download', $record->uuid)
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (Certificate $record) => ! $record->revoked),

                Tables\Actions\Action::make('verify')
                    ->label('Vérifier')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->url(fn (Certificate $record) =>
                        route('certificates.verify.page', $record->uuid)
                    )
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('download_zip')
                    ->label('📦 Télécharger en ZIP')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Télécharger les certificats sélectionnés')
                    ->modalDescription('Un fichier ZIP contenant tous les PDF sera téléchargé.')
                    ->action(function (\Illuminate\Support\Collection $records) {
                        $ids = $records->pluck('id')->implode(',');

                        // Redirige vers la route de téléchargement ZIP
                        return redirect()->to(
                            route('certificates.download-zip', ['ids' => $ids])
                        );
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('issued_at', 'desc');
    }

    // Désactive la création manuelle (les certificats sont générés automatiquement)
    public static function canCreate(): bool
    {
        return false;
    }

    // Désactive l'édition
    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificates::route('/'),
        ];
    }
}