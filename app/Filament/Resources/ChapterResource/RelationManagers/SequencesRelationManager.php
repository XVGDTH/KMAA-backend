<?php

namespace App\Filament\Resources\ChapterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Jobs\SplitCourseVideo;
use App\Models\VideoSplit;
use Filament\Tables\Actions\Action;

class SequencesRelationManager extends RelationManager
{
    protected static string $relationship = 'sequences';
    protected static ?string $title = 'Séquences';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('Titre de la séquence')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('type')
                ->label('Type')
                ->options([
                    'video' => 'Vidéo',
                    'quiz' => 'Quiz',
                    'devoir' => 'Devoir',
                    'tp' => 'TP',
                    'td' => 'TD',
                    'document' => 'Document',
                ])
                ->required()
                ->live(),

            Forms\Components\TextInput::make('duration')
                ->label('Durée (ex: 12 min)')
                ->visible(fn (Forms\Get $get) => $get('type') === 'video'),

            Forms\Components\TextInput::make('video_url')
                ->label('URL de la vidéo')
                ->url()
                ->visible(fn (Forms\Get $get) => $get('type') === 'video'),

            Forms\Components\FileUpload::make('document_url')
                ->label('Document (PDF, support...)')
                ->directory('sequences-documents')
                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['document'])),

            Forms\Components\Textarea::make('instructions')
                ->label('Consignes')
                ->rows(3)
                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['devoir', 'tp', 'td'])),

            Forms\Components\Repeater::make('quiz_questions')
                ->label('Questions du quiz')
                ->visible(fn (Forms\Get $get) => $get('type') === 'quiz')
                ->schema([
                    Forms\Components\TextInput::make('question')->required(),
                    Forms\Components\Repeater::make('options')
                        ->simple(Forms\Components\TextInput::make('option')->required())
                        ->label('Options')
                        ->minItems(2),
                    Forms\Components\TextInput::make('correctIndex')
                        ->label('Index de la bonne réponse (0, 1, 2...)')
                        ->numeric()
                        ->required(),
                ])
                ->collapsible(),

            Forms\Components\TextInput::make('position')
                ->label('Ordre d\'affichage')
                ->numeric()
                ->default(0),
        ]);
    }

    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->recordTitleAttribute('title')
    //         ->columns([
    //             Tables\Columns\TextColumn::make('title')->label('Titre'),
    //             Tables\Columns\TextColumn::make('type')->label('Type')->badge(),
    //             Tables\Columns\TextColumn::make('position')->label('Ordre')->sortable(),
    //         ])
    //         ->defaultSort('position')
    //         ->headerActions([
    //             Tables\Actions\CreateAction::make(),
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //             Tables\Actions\DeleteAction::make(),
    //         ]);
    // }

    public function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('title')
        ->columns([
            Tables\Columns\TextColumn::make('title')
                ->label('Titre'),
            Tables\Columns\TextColumn::make('type')
                ->label('Type')
                ->badge(),
            Tables\Columns\TextColumn::make('position')
                ->label('Ordre')
                ->sortable(),
        ])
        ->defaultSort('position')
        ->headerActions([
            Tables\Actions\CreateAction::make(),

            Action::make('importerVideoLongue')
                ->label('Importer une vidéo longue (découpage auto)')
                ->icon('heroicon-o-scissors')
                ->form([
                    Forms\Components\FileUpload::make('video')
                        ->label('Fichier vidéo (max 1h)')
                        ->disk('public')
                        ->directory('videos-originales')
                        ->acceptedFileTypes(['video/mp4', 'video/quicktime'])
                        ->required(),

                    Forms\Components\Select::make('segment_minutes')
                        ->label('Durée de chaque séquence')
                        ->options([
                            10 => '10 minutes',
                            12 => '12 minutes',
                            15 => '15 minutes',
                            20 => '20 minutes',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $split = VideoSplit::create([
                        'chapter_id' => $this->getOwnerRecord()->id,
                        'original_path' => $data['video'],
                        'segment_minutes' => $data['segment_minutes'],
                        'status' => 'en_attente',
                    ]);

                    SplitCourseVideo::dispatch($split);
                })
                ->modalSubmitActionLabel('Lancer le découpage')
                ->successNotificationTitle(
                    'Découpage lancé en arrière-plan. Les séquences apparaîtront dans quelques minutes.'
                ),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
}
}