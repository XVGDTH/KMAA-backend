<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Filament\Resources\ChapterResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

    protected static ?string $title = 'Chapitres';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')

            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Chapitre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Ordre')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sequences_count')
                    ->counts('sequences')
                    ->label('Séquences'),
            ])

            ->defaultSort('position')

            ->headerActions([
                Tables\Actions\Action::make('nouveauChapitre')
                    ->label('Nouveau chapitre')
                    ->icon('heroicon-o-plus')
                    ->url(fn () => ChapterResource::getUrl('create')),
            ])

            ->actions([
                Tables\Actions\Action::make('gererSequences')
                    ->label('Gérer les séquences')
                    ->icon('heroicon-o-list-bullet')
                    ->url(
                        fn ($record) => ChapterResource::getUrl(
                            'edit',
                            ['record' => $record]
                        )
                    ),

                Tables\Actions\EditAction::make()
                    ->url(
                        fn ($record) => ChapterResource::getUrl(
                            'edit',
                            ['record' => $record]
                        )
                    ),

                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
