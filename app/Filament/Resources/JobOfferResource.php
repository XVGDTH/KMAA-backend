<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobOfferResource\Pages;
use App\Models\JobOffer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobOfferResource extends Resource
{
    protected static ?string $model = JobOffer::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Offres d\'emploi';
    protected static ?string $modelLabel = 'Offre d\'emploi';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Informations principales')->columns(2)->schema([
                Forms\Components\TextInput::make('title')->label('Intitulé du poste')->required(),

                Forms\Components\Select::make('type')->label('Type de contrat')->required()
                    ->options([
                        'cdi' => 'CDI',
                        'cdd' => 'CDD',
                        'stage' => 'Stage',
                        'freelance' => 'Freelance',
                        'partenaire' => 'Partenariat',
                    ]),

                Forms\Components\TextInput::make('location')->label('Lieu (ex : Ouagadougou, Remote…)'),
                Forms\Components\TextInput::make('department')->label('Département (ex : Conseil, Formation…)'),

                Forms\Components\Textarea::make('excerpt')
                    ->label('Résumé de l\'offre')->required()->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Détails de l\'offre')->schema([
                Forms\Components\RichEditor::make('description')
                    ->label('Le poste (description)')->columnSpanFull(),

                Forms\Components\Repeater::make('missions')->label('Missions')
                    ->schema([
                        Forms\Components\TextInput::make('item')->label('Mission')
                            ->required()
                            ->afterStateHydrated(function (Forms\Set $set, ?string $state) {
                                if ($state !== null && ! is_string($state)) {
                                    $set('item', '');
                                }
                            }),
                    ])
                    ->columns(1),

                Forms\Components\Repeater::make('profile')->label('Profil recherché')
                    ->schema([
                        Forms\Components\TextInput::make('item')->label('Élément')->required(),
                    ])
                    ->columns(1),

                Forms\Components\TagsInput::make('tags')->label('Mots-clés')->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Visuel')->columns(2)->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('Image de l\'offre')->image()
                    ->disk('public')->directory('emplois')->imageEditor(),

                Forms\Components\TextInput::make('image_alt')
                    ->label("Texte alternatif de l'image"),
            ]),

            Forms\Components\Section::make('Publication')->columns(2)->schema([
                Forms\Components\Toggle::make('featured')->label('À la une'),
                Forms\Components\Toggle::make('published')->label('Publiée')->default(true),
                Forms\Components\DateTimePicker::make('published_at')->default(now()),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')->circular()
                    ->defaultImageUrl('https://placehold.co/80x80/e4e7ec/6b7280?text=%20'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Intitulé')->searchable()->sortable()->limit(50),

                Tables\Columns\TextColumn::make('type')
                    ->label('Contrat')->badge()->color('success'),

                Tables\Columns\TextColumn::make('location')->label('Lieu'),
                Tables\Columns\TextColumn::make('department')->label('Département'),

                Tables\Columns\IconColumn::make('featured')->label('Une')->boolean(),
                Tables\Columns\IconColumn::make('published')->label('Publiée')->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publiée le')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('published')->label('Publiée'),
                Tables\Filters\TernaryFilter::make('featured')->label('À la une'),
                Tables\Filters\SelectFilter::make('type')->label('Contrat')->options([
                    'cdi' => 'CDI', 'cdd' => 'CDD', 'stage' => 'Stage',
                    'freelance' => 'Freelance', 'partenaire' => 'Partenariat',
                ]),
            ])
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
        // ⚠️ ADAPTE aux noms réels générés (dir le dossier Pages/)
        return [
            'index' => Pages\ListJobOffers::route('/'),
            'create' => Pages\CreateJobOffer::route('/create'),
            'edit' => Pages\EditJobOffer::route('/{record}/edit'),
        ];
    }
}