<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterclassResource\Pages;
use App\Models\Masterclass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterclassResource extends Resource
{
    protected static ?string $model = Masterclass::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Masterclass';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Événement')->columns(2)->schema([
                Forms\Components\TextInput::make('category')->label('Sur-titre')->default('MASTERCLASS'),
                Forms\Components\TextInput::make('title')->label('Titre')->required(),

                Forms\Components\TextInput::make('subtitle')->label('Sous-titre')->columnSpanFull(),
                Forms\Components\RichEditor::make('description')->label('Description')->columnSpanFull(),

                Forms\Components\TextInput::make('date_label')->label('Date affichée (ex : Samedi 12 septembre 2026)'),
                Forms\Components\TextInput::make('time_label')->label('Horaire (ex : 18:00 – 20:00)'),
                Forms\Components\TextInput::make('timezone')->label('Fuseau')->default('GMT'),
                Forms\Components\TextInput::make('language')->label('Langue')->default('Français'),

                Forms\Components\TextInput::make('participants')->label('Participants actuels')->numeric()->default(0),
                Forms\Components\TextInput::make('capacity')->label('Capacité')->numeric()->default(500),

                Forms\Components\Select::make('status')->label('Statut de la session')->default('upcoming')
                    ->options([
                        'upcoming' => 'À venir',
                        'live' => 'En direct',
                        'replay' => 'Replay',
                    ]),

                Forms\Components\TextInput::make('youtube_id')
                    ->label('ID YouTube de la vidéo affichée')
                    ->hint('Ex : qp0HIF3SfI4'),

                Forms\Components\Toggle::make('published')->label('Publiée')->default(true),
            ]),

            Forms\Components\Section::make('Intervenant')->columns(2)->schema([
                Forms\Components\TextInput::make('speaker_name')->label('Nom'),
                Forms\Components\TextInput::make('speaker_role')->label('Fonction'),
                Forms\Components\TextInput::make('speaker_organization')->label('Organisation'),
                Forms\Components\FileUpload::make('speaker_image')
                    ->label('Photo')->image()->disk('public')->directory('masterclass'),
                Forms\Components\Textarea::make('speaker_bio')->label('Bio')->columnSpanFull(),

                Forms\Components\Repeater::make('speaker_stats')->label('Statistiques de l\'expert')
                    ->schema([
                        Forms\Components\TextInput::make('value')->label('Chiffre')->required(),
                        Forms\Components\TextInput::make('label')->label('Libellé')->required(),
                    ])->columns(2),
            ]),

            Forms\Components\Section::make('Programme (timeline)')->schema([
                Forms\Components\Repeater::make('sessions')->label('Séquences')
                    ->schema([
                        Forms\Components\TextInput::make('time')->label('Heure (18:00)')->required(),
                        Forms\Components\TextInput::make('duration')->label('Durée (25 min)'),
                        Forms\Components\TextInput::make('title')->label('Titre')->required(),
                        Forms\Components\Textarea::make('description')->label('Description'),
                        Forms\Components\Select::make('status')->label('Statut')
                            ->options(['done' => 'Terminé', 'live' => 'En cours', 'next' => 'À venir'])
                            ->default('next'),
                    ])->columns(2),
            ]),

            Forms\Components\Section::make('Vidéos (bibliothèque)')->schema([
                Forms\Components\Repeater::make('videos')->label('Vidéos YouTube')
                    ->schema([
                        Forms\Components\TextInput::make('id')->label('ID YouTube')->required()
                            ->hint('Ex : qp0HIF3SfI4'),
                        Forms\Components\TextInput::make('title')->label('Titre')->required(),
                        Forms\Components\TextInput::make('speaker')->label('Conférencier'),
                        Forms\Components\TextInput::make('duration')->label('Durée (18:04)'),
                        Forms\Components\TextInput::make('category')->label('Catégorie'),
                        Forms\Components\Textarea::make('description')->label('Description'),
                    ])->columns(2),
            ]),

            Forms\Components\Section::make('Ressources téléchargeables')->schema([
                Forms\Components\Repeater::make('resources')->label('Ressources')
                    ->schema([
                        Forms\Components\TextInput::make('icon')->label('Icône (emoji)')->default('📘'),
                        Forms\Components\TextInput::make('title')->label('Titre')->required(),
                        Forms\Components\TextInput::make('type')->label('Type (PDF…)'),
                        Forms\Components\TextInput::make('size')->label('Taille (2.4 MB)'),
                    ])->columns(2),
            ]),

            Forms\Components\Section::make('Quiz de validation')->schema([
                Forms\Components\Repeater::make('quiz')->label('Questions')
                    ->schema([
                        Forms\Components\Textarea::make('question')->label('Question')->required()->columnSpanFull(),
                        Forms\Components\TagsInput::make('options')->label('Options (4 réponses)')->required(),
                        Forms\Components\Select::make('correct')->label('Index de la bonne réponse')->required()
                            ->options([0 => 'A (1ère)', 1 => 'B (2ème)', 2 => 'C (3ème)', 3 => 'D (4ème)'])
                            ->default(0),
                    ])->columns(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Titre')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('date_label')->label('Date'),
                Tables\Columns\TextColumn::make('status')->label('Statut')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'live' => 'danger', 'replay' => 'info', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('speaker_name')->label('Expert'),
                Tables\Columns\IconColumn::make('published')->label('Publiée')->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
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

    public static function getPages(): array
    {
        // ⚠️ ADAPTE au dir réel (CreateMasterclass / EditMasterclass attendus)
        return [
            'index' => Pages\ListMasterclasses::route('/'),
            'create' => Pages\CreateMasterclass::route('/create'),
            'edit' => Pages\EditMasterclass::route('/{record}/edit'),
        ];
    }
}