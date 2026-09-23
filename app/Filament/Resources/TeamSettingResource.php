<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamSettingResource\Pages;
use App\Models\TeamSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamSettingResource extends Resource
{
    protected static ?string $model = TeamSetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Page Équipe (textes)';
    protected static ?string $modelLabel = 'Texte page Équipe';

    // ============================================================
    // ✅ MASQUE CETTE RESOURCE DE LA SIDEBAR
    //    (accès via le bouton « Textes de la page » dans la liste Équipe)
    // ============================================================
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        // Une seule ligne de réglages : pas de création manuelle
        return TeamSetting::count() === 0;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Hero')->schema([
                Forms\Components\TextInput::make('hero_eyebrow')->label('Sur-titre'),
                Forms\Components\TextInput::make('hero_title')->label('Titre (partie 1)')->required(),
                Forms\Components\TextInput::make('hero_title_accent')->label('Titre (partie dorée)'),
                Forms\Components\Textarea::make('hero_lead')->label('Paragraphe d\'intro')->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Statistiques (3 blocs)')->schema([
                Forms\Components\Repeater::make('stats')->label('Statistiques')
                    ->schema([
                        Forms\Components\TextInput::make('value')->label('Chiffre')->required(),
                        Forms\Components\TextInput::make('label')->label('Libellé')->required(),
                    ])
                    ->columns(2)->defaultItems(3),
            ]),

            Forms\Components\Section::make('Culture')->schema([
                Forms\Components\TextInput::make('culture_title')->label('Titre'),
                Forms\Components\Textarea::make('culture_text')->label('Texte')->columnSpanFull(),

                Forms\Components\Repeater::make('culture_values')->label('Valeurs (3 blocs)')
                    ->schema([
                        Forms\Components\TextInput::make('number')->label('Numéro')->default('01'),
                        Forms\Components\TextInput::make('title')->label('Titre')->required(),
                        Forms\Components\Textarea::make('text')->label('Texte'),
                    ])
                    ->columns(3)->defaultItems(3),
            ]),

            Forms\Components\Section::make('Appel à l\'action (CTA)')->schema([
                Forms\Components\TextInput::make('cta_title')->label('Titre CTA'),
                Forms\Components\Textarea::make('cta_text')->label('Texte CTA')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hero_title')->label('Titre de la page'),
                Tables\Columns\TextColumn::make('updated_at')->label('Modifié le')->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeamSettings::route('/'),
            'create' => Pages\CreateTeamSetting::route('/create'),
            'edit' => Pages\EditTeamSetting::route('/{record}/edit'),
        ];
    }
}