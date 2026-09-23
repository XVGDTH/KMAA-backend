<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobApplicationResource\Pages;
use App\Models\JobApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;


class JobApplicationResource extends Resource
{
    protected static ?string $model = JobApplication::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationLabel = 'Candidatures';
    protected static ?string $modelLabel = 'Candidature';

    // ✅ Badge 🆕 avec le nombre de candidatures non traitées
    public static function getNavigationBadge(): ?string
    {
        return (string) JobApplication::where('status', 'nouvelle')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    // ✅ Les candidatures viennent du SITE : pas de création manuelle admin
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Candidat')->columns(2)->schema([
                Forms\Components\TextInput::make('full_name')->label('Nom complet')->disabled(),
                Forms\Components\TextInput::make('email')->label('Email')->disabled(),
                Forms\Components\TextInput::make('phone')->label('Téléphone')->disabled(),
                Forms\Components\Select::make('job_offer_id')
                    ->label('Offre visée')
                    ->relationship('jobOffer', 'title')
                    ->disabled(),
                Forms\Components\Textarea::make('message')->label('Message')->disabled()->columnSpanFull(),
                                Forms\Components\FileUpload::make('cv_path')
                    ->label('CV du candidat')
                    ->disk('public')                 // ✅ lit storage/app/public/...
                    ->directory('candidatures')      // ✅ où sont les fichiers
                    ->downloadable()                 // ✅ bouton télécharger
                    ->openable()                     // ✅ bouton ouvrir dans un onglet
                    ->dehydrated(false)              // ✅ pas de réécriture à l'édition
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Traitement')->schema([
                Forms\Components\Select::make('status')->label('Statut')->required()
                    ->options([
                        'nouvelle' => 'Nouvelle',
                        'vue' => 'Vue',
                        'retenu' => 'Retenu(e) ✅',
                        'refuse' => 'Refusée',
                    ])
                    ->native(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Candidat')->searchable()->sortable()
                    ->weight('bold'),

                // Tables\Columns\TextColumn::make('jobOffer.title')
                //     ->label('Offre')->limit(40),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')->badge()
                    ->color(fn (string $state) => $state === 'spontanee' ? 'info' : 'gray')
                    ->formatStateUsing(fn (string $state) =>
                        $state === 'spontanee' ? '✉️ Spontanée' : '📋 Offre'),

                Tables\Columns\TextColumn::make('jobOffer.title')
                    ->label('Offre')->limit(40)
                    ->placeholder('— Spontanée —'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Tél')->placeholder('—'),
                                Tables\Columns\TextColumn::make('cv_path')
                    ->label('CV')
                    ->formatStateUsing(fn () => '📄 Voir le CV')
                    ->color('info')
                    ->url(
                        fn (JobApplication $record) =>
                            $record->cv_path
                                ? asset('storage/' . $record->cv_path)
                                : null
                    )
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'nouvelle' => 'danger',
                        'vue' => 'warning',
                        'retenu' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'nouvelle' => '🆕 Nouvelle',
                        'vue' => 'Vue',
                        'retenu' => 'Retenue',
                        'refuse' => 'Refusée',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reçue le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            // ->filters([
            //     Tables\Filters\SelectFilter::make('status')->label('Statut')->options([
            //         'nouvelle' => 'Nouvelle', 'vue' => 'Vue',
            //         'retenu' => 'Retenue', 'refuse' => 'Refusée',
            //     ]),
            //     Tables\Filters\SelectFilter::make('jobOffer')->label('Offre')
            //         ->relationship('jobOffer', 'title'),
            // ])
                ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Statut')->options([
                    'nouvelle' => 'Nouvelle', 'vue' => 'Vue',
                    'retenu' => 'Retenue', 'refuse' => 'Refusée',
                ]),

                Tables\Filters\SelectFilter::make('type')->label('Type')->options([
                    'offre' => 'Candidatures à une offre',
                    'spontanee' => 'Candidatures spontanées',
                ]),

                Tables\Filters\SelectFilter::make('jobOffer')->label('Offre')
                    ->relationship('jobOffer', 'title'),
            ])
            ->actions([
                // ✅ Changement de statut rapide, sans ouvrir l'édition
                Tables\Actions\Action::make('marquerVue')
                    ->label('👀')->tooltip('Marquer comme vue')
                    ->visible(fn (JobApplication $r) => $r->status === 'nouvelle')
                    ->action(fn (JobApplication $r) => $r->update(['status' => 'vue'])),

                Tables\Actions\Action::make('retenir')
                    ->label('✅')->tooltip('Retenir la candidature')
                    ->color('success')
                    ->visible(fn (JobApplication $r) => $r->status !== 'retenu')
                    ->action(fn (JobApplication $r) => $r->update(['status' => 'retenu'])),

                Tables\Actions\Action::make('refuser')
                    ->label('❌')->tooltip('Refuser')
                    ->color('danger')
                    ->visible(fn (JobApplication $r) => $r->status !== 'refuse')
                    ->requiresConfirmation()
                    ->action(fn (JobApplication $r) => $r->update(['status' => 'refuse'])),

                Tables\Actions\EditAction::make()->label('Détails'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobApplications::route('/'),
            'create' => Pages\CreateJobApplications::route('/create'),
            'edit' => Pages\EditJobApplications::route('/{record}/edit'),
        ];
    }
}