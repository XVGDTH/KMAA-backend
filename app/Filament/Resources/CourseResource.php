<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CourseResource\RelationManagers\ChaptersRelationManager;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Identification')->columns(2)->schema([
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('title')
                        ->label('Titre du cours')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('subject')
                        ->label('Matière')
                        ->options([
                            'affaires' => 'Affaires et gestion',
                            'comptabilite' => 'Comptabilité et finance',
                            'psychologie' => 'Psychologie',
                            'sante' => 'Soins de santé',
                            'donnees' => 'Analyse de données',
                            'informatique' => 'Informatique et technologie',
                            'droit' => 'Droit',
                            'direction' => 'Direction et leadership',
                            'marketing' => 'Marketing',
                            'enseignement' => 'Enseignement et pédagogie',
                            'langues' => 'Langues',
                            'rh' => 'Ressources humaines',
                            'audit' => 'Audit et contrôle de gestion',
                            'communication' => 'Communication',
                            'entrepreneuriat' => 'Entrepreneuriat',
                            'developpement-durable' => 'Développement durable',
                            'ingenierie' => 'Ingénierie',
                            'design' => 'Design et créativité',
                        ])
                        ->required()
                        ->searchable()
                        ->native(false),

                    Forms\Components\Select::make('category')
                        ->label('Catégorie')
                        ->options([
                            'formation-courte' => 'Formation courte',
                            'formation-certifiante' => 'Formation certifiante',
                            'masterclass' => 'Masterclass',
                            'e-learning' => 'E-learning',
                            'entreprise' => 'Formation entreprise',
                            'bootcamp' => 'Bootcamp',
                        ])
                        ->required()
                        ->searchable()
                        ->native(false),

                    Forms\Components\Select::make('level')
                        ->label('Niveau')
                        ->options([
                            'debutant' => 'Débutant',
                            'intermediaire' => 'Intermédiaire',
                            'avance' => 'Avancé',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->columnSpanFull(),
                ]),

                // ============================================================
                // 🎓 DOCUMENT DÉLIVRÉ — c'est LUI qui détermine le template
                //    PDF (Certificat ou Attestation) généré pour les
                //    apprenants ayant terminé ce cours.
                // ============================================================
                Forms\Components\Section::make('Document de fin de parcours')->columns(2)->schema([

                    Forms\Components\Select::make('diploma')
                        ->label('Document délivré à la réussite')
                        ->options([
                            'certificate' => 'Certificat',
                            'attestation' => 'Attestation',
                        ])
                        ->default('certificate')
                        ->required()
                        ->native(false)
                        ->helperText('Détermine automatiquement le template PDF généré pour les apprenants ayant terminé ce cours.'),

                    Forms\Components\Toggle::make('has_certificate')
                        ->label('Un document est délivré à la fin')
                        ->helperText('Certificat (réussite) ou Attestation (participation), selon le choix ci-dessus.')
                        ->default(true)
                        ->required(),

                    Forms\Components\TextInput::make('promotion_label')
                        ->label('Promotion (optionnel)')
                        ->placeholder('ex : Promotion Septembre 2026')
                        ->nullable(),
                ]),

                // ============================================================
                // DURÉES
                // ============================================================
                Forms\Components\Section::make('Durées')->columns(2)->schema([
                    Forms\Components\TextInput::make('duration_full')
                        ->label('Durée — formation initiale')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('duration_alternance')
                        ->label('Durée — alternance')
                        ->required()
                        ->maxLength(255),
                ]),

                // ============================================================
                // IMAGE (upload ou URL)
                // ============================================================
                Forms\Components\Section::make('Image de couverture')->schema([
                    Forms\Components\Radio::make('image_source')
                        ->label('Source de l\'image')
                        ->options([
                            'upload' => 'Importer une image',
                            'url' => 'Utiliser un lien (URL)',
                        ])
                        ->default('url')
                        ->inline()
                        ->live()
                        ->dehydrated(false), // pas une colonne en base

                    Forms\Components\FileUpload::make('image_upload')
                        ->label('Importer une image')
                        ->image()
                        ->directory('courses')
                        ->visibility('public')
                        ->imageEditor()
                        ->visible(fn (Forms\Get $get) => $get('image_source') === 'upload')
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('image_url')
                        ->label('URL de l\'image')
                        ->url()
                        ->maxLength(2048)
                        ->placeholder('https://images.unsplash.com/...')
                        ->visible(fn (Forms\Get $get) => $get('image_source') === 'url')
                        ->dehydrated(false),

                    // Champ réel en base (masqué, rempli à la sauvegarde)
                    Forms\Components\Hidden::make('image'),

                    Forms\Components\TextInput::make('image_alt')
                        ->label('Texte alternatif')
                        ->maxLength(255),
                ]),

                // ============================================================
                // FORMATEUR + OPTIONS
                // ============================================================
                Forms\Components\Section::make('Encadrement & mise en avant')->columns(2)->schema([
                    Forms\Components\Select::make('instructor_id')
                        ->label('Formateur')
                        ->relationship(
                            name: 'instructor',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) =>
                                $query->role('formateur')
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),

                    Forms\Components\Toggle::make('popular')
                        ->label('Cours populaire')
                        ->required(),

                    Forms\Components\TextInput::make('badge')
                        ->label('Badge (ex : Nouveau, Best-seller)')
                        ->maxLength(255)
                        ->default(null),

                    Forms\Components\Toggle::make('is_free')
                        ->label('Cours gratuit')
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular(false)
                    ->height(40),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Matière')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie'),

                Tables\Columns\TextColumn::make('level')
                    ->label('Niveau'),

                Tables\Columns\TextColumn::make('diploma')
                    ->label('Document délivré')
                    ->badge()
                    ->color(fn (string $state) => $state === 'attestation' ? 'warning' : 'success')
                    ->formatStateUsing(fn (string $state) =>
                        $state === 'attestation' ? 'Attestation' : 'Certificat'),

                Tables\Columns\TextColumn::make('duration_full')
                    ->label('Durée initiale'),

                Tables\Columns\TextColumn::make('duration_alternance')
                    ->label('Durée alternance'),

                Tables\Columns\TextColumn::make('instructor.name')
                    ->label('Formateur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('popular')->label('Populaire')->boolean(),
                Tables\Columns\IconColumn::make('is_free')->label('Gratuit')->boolean(),
                Tables\Columns\IconColumn::make('has_certificate')->label('Document fin')->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('diploma')->label('Document délivré')->options([
                    'certificate' => 'Certificat',
                    'attestation' => 'Attestation',
                ]),
                Tables\Filters\TernaryFilter::make('has_certificate')->label('Document de fin'),
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

    public static function getRelations(): array
    {
        return [
            ChaptersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}