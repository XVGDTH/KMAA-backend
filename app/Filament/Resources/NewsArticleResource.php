<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsArticleResource\Pages;
use App\Models\NewsArticle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsArticleResource extends Resource
{
    protected static ?string $model = NewsArticle::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'Actualités';
    protected static ?string $modelLabel = 'Actualité';

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
                    ->label('Contenu de l\'article')->columnSpanFull(),

                Forms\Components\Select::make('category')->label('Catégorie')->required()
                    ->options([
                        'formation' => 'Formation',
                        'entreprise' => 'Entreprise',
                        'evenement' => 'Événement',
                        'ressources' => 'Ressources',
                        'vie-kmaa' => 'Vie KMAA',
                        'carriere' => 'Carrière',
                    ]),

                Forms\Components\FileUpload::make('image')
                    ->image()->disk('public')->directory('actualites')
                    ->imageEditor(),

                Forms\Components\TextInput::make('image_alt')
                    ->label("Texte alternatif de l'image"),

                Forms\Components\TextInput::make('read_time')
                    ->label('Lecture')->numeric()->suffix('min')->default(4),
            ]),

            Forms\Components\Section::make('Auteur')->columns(2)->schema([
                Forms\Components\TextInput::make('author_name')->label('Nom affiché'),
                Forms\Components\TextInput::make('author_role')->label('Rôle affiché'),
                Forms\Components\TextInput::make('author_avatar')->label('Avatar (URL)')->url()->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Publication')->columns(2)->schema([
                Forms\Components\Toggle::make('featured')->label('À la une (max 3)'),
                Forms\Components\Toggle::make('published')->label('Publié')->default(true),
                Forms\Components\DateTimePicker::make('published_at')->default(now()),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')->disk('public')->circular()
                    ->defaultImageUrl('https://placehold.co/80x80/e4e7ec/6b7280?text=%20'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')->searchable()->sortable()->limit(60),

                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')->badge()->color('info'),

                Tables\Columns\TextColumn::make('author_name')
                    ->label('Auteur')->placeholder('—'),

                Tables\Columns\TextColumn::make('read_time')
                    ->label('Lecture')->suffix(' min'),

                Tables\Columns\IconColumn::make('featured')->label('Une')->boolean(),
                Tables\Columns\IconColumn::make('published')->label('Publié')->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publié le')->dateTime('d/m/Y')->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsArticles::route('/'),
            'create' => Pages\CreateNewsArticle::route('/create'),
            'edit' => Pages\EditNewsArticle::route('/{record}/edit'),
        ];
    }
}