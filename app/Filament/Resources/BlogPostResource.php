<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // ============================================================
    // FORMULAIRE (création / édition)
    // ============================================================
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Contenu')->columns(2)->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Titre')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state)
                        => $set('slug', \Illuminate\Support\Str::slug($state ?? ''))),

                Forms\Components\TextInput::make('slug')
                    ->required()->maxLength(255)->unique(ignoreRecord: true),

                Forms\Components\Textarea::make('excerpt')
                    ->label('Résumé')->required()->columnSpanFull(),

                Forms\Components\RichEditor::make('content')
                    ->label('Contenu complet')->columnSpanFull(),

                Forms\Components\Select::make('category')
                    ->label('Catégorie')->required()
                    ->options([
                        'finance' => 'Finance',
                        'comptabilite' => 'Comptabilité',
                        'fiscalite' => 'Fiscalité',
                        'audit' => 'Audit',
                        'gestion' => 'Gestion',
                        'entreprise' => 'Entreprise',
                        'carriere' => 'Carrière',
                        'formation' => 'Formation',
                    ]),

                Forms\Components\Select::make('kind')
                    ->label('Format')->default('article')->required()
                    ->options([
                        'article' => 'Article',
                        'guide' => 'Guide',
                        'actualite' => 'Actualité',
                        'conseil' => 'Conseil',
                    ]),

                Forms\Components\FileUpload::make('image')
                    ->image()->disk('public')->directory('blog')
                    ->imageEditor()->columnSpanFull(),

                Forms\Components\TextInput::make('image_alt')
                    ->label("Texte alternatif de l'image")
                    ->hint('Décris l\'image pour l\'accessibilité')
                    ->columnSpanFull(),

                Forms\Components\Select::make('level')->label('Niveau')
                    ->options([
                        'Débutant' => 'Débutant',
                        'Intermédiaire' => 'Intermédiaire',
                        'Avancé' => 'Avancé',
                    ]),

                Forms\Components\TextInput::make('audience')->label('Audience (ex : PME, Dirigeants…)'),

                Forms\Components\TagsInput::make('tags')->label('Tags')->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Auteur')->columns(2)->schema([
                Forms\Components\Select::make('author_id')
                    ->label('Compte auteur (optionnel)')
                    ->relationship('author', 'name')->searchable()->preload(),

                Forms\Components\TextInput::make('author_name')->label('Nom affiché'),
                Forms\Components\TextInput::make('author_role')->label('Rôle affiché'),
                Forms\Components\TextInput::make('author_avatar')->label('Avatar (URL)')->url(),
            ]),

            Forms\Components\Section::make('Publication')->columns(2)->schema([
                Forms\Components\TextInput::make('read_time')
                    ->label('Temps de lecture')->numeric()->suffix('min')->default(5),

                Forms\Components\Toggle::make('featured')->label('À la une'),
                Forms\Components\Toggle::make('popular')->label('Populaire'),
                Forms\Components\Toggle::make('published')->label('Publié')->default(true),
                Forms\Components\DateTimePicker::make('published_at')->default(now()),
            ]),
        ]);
    }

    // ============================================================
    // TABLE (la liste — C'EST CE QU'IL MANQUAIT !)
    // ============================================================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl('https://placehold.co/80x80/e4e7ec/6b7280?text=%20'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('kind')
                    ->label('Format')
                    ->badge(),

                Tables\Columns\TextColumn::make('author_name')
                    ->label('Auteur')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('read_time')
                    ->label('Lecture')
                    ->suffix(' min'),

                Tables\Columns\IconColumn::make('featured')->label('Une')->boolean(),
                Tables\Columns\IconColumn::make('popular')->label('Populaire')->boolean(),
                Tables\Columns\IconColumn::make('published')->label('Publié')->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('published')->label('Publié'),
                Tables\Filters\TernaryFilter::make('featured')->label('À la une'),
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

    // ============================================================
    // RELATIONS & PAGES
    // ============================================================
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}