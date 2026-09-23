<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMemberResource\Pages;
use App\Models\TeamMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Équipe';
    protected static ?string $modelLabel = 'Membre de l\'équipe';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Membre')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->label('Nom complet')->required(),
                Forms\Components\TextInput::make('role')->label('Fonction')->required(),

                Forms\Components\Select::make('department')->label('Département')->required()
                    ->options([
                        'direction' => 'Direction',
                        'conseil' => 'Conseil',
                        'formation' => 'Formation',
                        'support' => 'Support',
                    ]),

                Forms\Components\FileUpload::make('image')
                    ->label('Photo')->image()->disk('public')->directory('equipe')
                    ->imageEditor(),

                Forms\Components\Textarea::make('bio')->label('Biographie')->columnSpanFull(),

                Forms\Components\TextInput::make('linkedin')->label('LinkedIn (URL)')->url(),

                Forms\Components\TextInput::make('image_alt')
                    ->label("Texte alternatif de l'image")
                    ->default(fn (Forms\Get $get) => $get('name')),
            ]),

            Forms\Components\Section::make('Affichage')->columns(2)->schema([
                Forms\Components\Toggle::make('featured')
                    ->label('Direction & associés (bloc vedette)'),

                Forms\Components\TextInput::make('position')
                    ->label('Ordre d\'affichage')->numeric()->default(1)->minValue(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Photo')->disk('public')->circular()
                    ->defaultImageUrl('https://placehold.co/80x80/e4e7ec/6b7280?text=%20'),

                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('role')->label('Fonction')->limit(40),

                Tables\Columns\TextColumn::make('department')
                    ->label('Département')->badge()->color('info'),

                Tables\Columns\IconColumn::make('featured')->label('Vedette')->boolean(),
                Tables\Columns\TextColumn::make('position')->label('Ordre')->sortable(),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => Pages\ListTeamMembers::route('/'),
    //         'create' => Pages\CreateTeamMembers::route('/create'),
    //         'edit' => Pages\EditTeamMembers::route('/{record}/edit'),
    //     ];
    // }

        public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/create'),
            'edit' => Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}