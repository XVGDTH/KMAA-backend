<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CourseResource\RelationManagers\ChaptersRelationManager;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '📚 Cours & Formations';

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ============================================================
            // TYPE DE COURS (INDIVIDUEL ou ENTREPRISE)
            // ============================================================
            Forms\Components\Section::make('Type de cours')->columns(2)->schema([
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'individual' => '📚 Cours individuel (catalogue)',
                        'enterprise' => '🏢 Formation entreprise (abonnement)',
                    ])
                    ->default('individual')
                    ->required()
                    ->live()
                    ->native(false),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]),

            // ============================================================
            // IDENTIFICATION
            // ============================================================
            Forms\Components\Section::make('Identification')->columns(2)->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Titre du cours')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

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
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('long_description')
                    ->label('Description longue (HTML)')
                    ->columnSpanFull(),
            ]),

            // ============================================================
            // CHAMPS SPÉCIFIQUES ENTREPRISE
            // ============================================================
            Forms\Components\Section::make('🏢 Informations Entreprise')
                ->visible(fn (Get $get) => $get('type') === 'enterprise')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('delivery_mode')
                        ->label('Mode de livraison')
                        ->options([
                            'en_ligne' => 'En ligne',
                            'presentiel' => 'Présentiel',
                            'mixte' => 'Mixte',
                        ])
                        ->native(false),

                    Forms\Components\TextInput::make('hours_per_week')
                        ->label('Rythme hebdomadaire')
                        ->placeholder('ex: 6h / semaine')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('modules_count')
                        ->label('Nombre de modules')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('enterprise_base_price')
                        ->label('Prix de base entreprise')
                        ->numeric()
                        ->prefix('FCFA'),
                ]),

            // ============================================================
            // DOCUMENT DE FIN DE PARCOURS
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
                    ->native(false),

                Forms\Components\Toggle::make('has_certificate')
                    ->label('Un document est délivré')
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
            // TARIFICATION
            // ============================================================
Forms\Components\Section::make('💰 Tarification')->columns(2)->schema([
    Forms\Components\TextInput::make('price')
        ->label('Prix (FCFA)')
        ->numeric()
        ->prefix('FCFA')
        ->default(0)
        ->required(),

    Forms\Components\TextInput::make('promo_price')
        ->label('Prix promo (FCFA)')
        ->numeric()
        ->prefix('FCFA')
        ->nullable(),

    Forms\Components\TextInput::make('promo_code')
        ->label('Code promo actif')
        ->maxLength(50)
        ->nullable(),
]),
            // ============================================================
            // IMAGE
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
                    ->dehydrated(false),

                Forms\Components\FileUpload::make('image_upload')
                    ->label('Importer une image')
                    ->image()
                    ->directory('courses')
                    ->visibility('public')
                    ->imageEditor()
                    ->visible(fn (Get $get) => $get('image_source') === 'upload')
                    ->dehydrated(false),

                Forms\Components\TextInput::make('image_url')
                    ->label('URL de l\'image')
                    ->url()
                    ->maxLength(2048)
                    ->visible(fn (Get $get) => $get('image_source') === 'url')
                    ->dehydrated(false),

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
                    ->nullable(),

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
                // ✅ NOUVELLE COLONNE TYPE
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'individual' => 'info',
                        'enterprise' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'individual' => '📚 Individuel',
                        'enterprise' => '🏢 Entreprise',
                        default => $state,
                    }),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular(false)
                    ->height(40),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Matière')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('level')
                    ->label('Niveau'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->money('XOF', locale: 'fr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('diploma')
                    ->label('Document')
                    ->badge()
                    ->color(fn (string $state) => $state === 'attestation' ? 'warning' : 'success')
                    ->formatStateUsing(fn (string $state) =>
                        $state === 'attestation' ? 'Attestation' : 'Certificat'),

                Tables\Columns\TextColumn::make('instructor.name')
                    ->label('Formateur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('popular')->label('Populaire')->boolean(),
                Tables\Columns\IconColumn::make('is_free')->label('Gratuit')->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // ✅ NOUVEAU FILTRE TYPE
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type de cours')
                    ->options([
                        'individual' => '📚 Cours individuel',
                        'enterprise' => '🏢 Formation entreprise',
                    ]),

                Tables\Filters\SelectFilter::make('subject')
                    ->label('Matière')
                    ->options(fn () => Course::distinct()->pluck('subject', 'subject')->toArray()),

                Tables\Filters\SelectFilter::make('level')
                    ->label('Niveau')
                    ->options([
                        'debutant' => 'Débutant',
                        'intermediaire' => 'Intermédiaire',
                        'avance' => 'Avancé',
                    ]),

                Tables\Filters\SelectFilter::make('diploma')
                    ->label('Document délivré')
                    ->options([
                        'certificate' => 'Certificat',
                        'attestation' => 'Attestation',
                    ]),

                Tables\Filters\TernaryFilter::make('popular')->label('Cours populaires'),
                Tables\Filters\TernaryFilter::make('is_free')->label('Cours gratuits'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}