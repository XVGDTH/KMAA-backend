<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoSplitResource\Pages;
use App\Jobs\SplitCourseVideo;
use App\Models\{Chapter, Course, VideoSplit};
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VideoSplitResource extends Resource
{
    protected static ?string $model = VideoSplit::class;
    protected static ?string $navigationIcon = 'heroicon-o-scissors';
    protected static ?string $navigationLabel = 'Découpage vidéo';
    protected static ?string $modelLabel = 'Découpage vidéo';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('course_id')
                ->label('Cours')
                ->options(Course::pluck('title', 'id'))
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn (Forms\Set $set) => $set('chapter_id', null))
                ->dehydrated(false), // champ virtuel, sert juste à filtrer les chapitres

            Forms\Components\Select::make('chapter_id')
                ->label('Chapitre où ajouter les séquences')
                ->options(function (Forms\Get $get) {
                    $courseId = $get('course_id');
                    if (! $courseId) return [];
                    return Chapter::where('course_id', $courseId)->pluck('title', 'id');
                })
                ->searchable()
                ->required()
                ->helperText('Choisissez d\'abord le cours ci-dessus.'),

            // Forms\Components\FileUpload::make('original_path')
            //     ->label('Vidéo à découper')
            //     ->disk('public')
            //     ->directory('videos-originales')
            //     ->acceptedFileTypes(['video/mp4', 'video/quicktime'])
            //     ->required(),
            Forms\Components\FileUpload::make('original_path')
    ->label('Vidéo à découper')
    ->disk('local') // ou ton disque configuré
    ->directory('video-splits')
    ->acceptedFileTypes(['video/mp4', 'video/quicktime'])
    // ->maxSize( 1 048 576 ) // en Ko → ici 500 Mo, ajuste selon tes besoins
    ->maxSize(1048576) // pour 1go d'abord en phase de test.
    ->required(),

            Forms\Components\Select::make('segment_minutes')
                ->label('Durée de chaque séquence')
                ->options([
                    10 => '10 minutes',
                    12 => '12 minutes',
                    15 => '15 minutes',
                    20 => '20 minutes',
                    25 => '25 minutes',
                    30 => '30 minutes',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('chapter.title')->label('Chapitre')->searchable(),
                Tables\Columns\TextColumn::make('chapter.course.title')->label('Cours'),
                Tables\Columns\TextColumn::make('segment_minutes')->label('Durée/séquence')->suffix(' min'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'termine' => 'success',
                        'echec' => 'danger',
                        'en_cours' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'en_attente' => 'En attente',
                        'en_cours' => 'En cours...',
                        'termine' => 'Terminé',
                        'echec' => 'Échec',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('error_message')->label('Erreur')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Lancé le')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideoSplits::route('/'),
            'create' => Pages\CreateVideoSplit::route('/create'),
        ];
    }
}