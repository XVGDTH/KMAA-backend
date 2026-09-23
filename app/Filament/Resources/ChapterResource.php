<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChapterResource\Pages;
use App\Filament\Resources\ChapterResource\RelationManagers\SequencesRelationManager;
use App\Models\Chapter;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Chapitres';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('course_id')
                ->label('Cours')
                ->options(Course::pluck('title', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('title')
                ->label('Titre du chapitre')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('position')
                ->label('Ordre d\'affichage')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.title')->label('Cours')->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Chapitre')->searchable(),
                Tables\Columns\TextColumn::make('position')->label('Ordre')->sortable(),
                Tables\Columns\TextColumn::make('sequences_count')->counts('sequences')->label('Séquences'),
            ])
            ->defaultSort('position')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SequencesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChapters::route('/'),
            'create' => Pages\CreateChapter::route('/create'),
            'edit' => Pages\EditChapter::route('/{record}/edit'),
        ];
    }
}