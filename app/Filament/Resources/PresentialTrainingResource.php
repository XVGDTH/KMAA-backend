<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresentialTrainingResource\Pages;
use App\Models\PresentialTraining;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PresentialTrainingResource extends Resource
{
    protected static ?string $model = PresentialTraining::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = '🎓 Formations';
    protected static ?string $navigationLabel = 'Formations présentielles';
    protected static ?string $modelLabel = 'Formation présentielle';
    protected static ?string $pluralModelLabel = 'Formations présentielles';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identité')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titre de la formation')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) =>
                            $set('slug', Str::slug($state))
                        ),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug URL')
                        ->required()
                        ->unique(ignoreRecord: true),

                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Dates et durée')
                ->schema([
                    Forms\Components\DatePicker::make('starts_at')
                        ->label('Date de début')
                        ->required(),

                    Forms\Components\DatePicker::make('ends_at')
                        ->label('Date de fin')
                        ->required()
                        ->afterOrEqual('starts_at'),

                    Forms\Components\TextInput::make('days_count')
                        ->label('Nombre de jours')
                        ->numeric()
                        ->required()
                        ->minValue(1),

                    Forms\Components\TextInput::make('hours_count')
                        ->label('Volume horaire (heures)')
                        ->numeric()
                        ->minValue(1),
                ])->columns(2),

            Forms\Components\Section::make('Lieu et formateur')
                ->schema([
                    Forms\Components\TextInput::make('location')
                        ->label('Lieu'),

                    Forms\Components\TextInput::make('instructor_name')
                        ->label('Nom du formateur'),

                    Forms\Components\TextInput::make('instructor_role')
                        ->label('Fonction du formateur'),
                ])->columns(2),

            Forms\Components\Section::make('Participants et document')
                ->schema([
                    Forms\Components\TextInput::make('expected_participants')
                        ->label('Nombre de participants prévus')
                        ->numeric()
                        ->minValue(1),

                    Forms\Components\Select::make('certificate_type')
                        ->label('Type de document délivré')
                        ->options([
                            'attestation' => '📜 Attestation',
                            'certificate' => '🏆 Certificat',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Toggle::make('registration_open')
                        ->label('Inscriptions ouvertes')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Formation')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_count')
                    ->label('Jours')
                    ->suffix(' j'),

                Tables\Columns\TextColumn::make('registered_count')
                    ->label('Inscrits')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('certificate_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'certificate' ? '🏆 Certificat' : '📜 Attestation')
                    ->color(fn ($state) => $state === 'certificate' ? 'warning' : 'info'),

                Tables\Columns\IconColumn::make('registration_open')
                    ->label('Ouvert')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('copy_link')
                    ->label('Copier le lien')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->action(function (PresentialTraining $record) {
                        // Side-effect : on copie côté UI grâce à JS injecté
                    })
                    ->extraAttributes(fn (PresentialTraining $record) => [
                        'x-on:click' => "window.navigator.clipboard.writeText('{$record->getRegistrationUrl()}'); window.dispatchEvent(new CustomEvent('notify', {detail:'Lien copié !'}))",
                    ]),

                Tables\Actions\Action::make('view_participants')
                    ->label('Participants')
                    ->icon('heroicon-o-users')
                    ->url(fn (PresentialTraining $record) =>
                        PresentialTrainingResource::getUrl('participants', ['record' => $record])
                    ),

                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('starts_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\PresentialTrainingResource\RelationManagers\ParticipantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'        => Pages\ListPresentialTrainings::route('/'),
            'create'       => Pages\CreatePresentialTraining::route('/create'),
            'edit'         => Pages\EditPresentialTraining::route('/{record}/edit'),
            'participants' => Pages\ViewParticipants::route('/{record}/participants'),
        ];
    }
}