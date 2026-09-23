<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingPlanResource\Pages;
use App\Models\PricingPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PricingPlanResource extends Resource
{
    protected static ?string $model = PricingPlan::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Formules de prix';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Formule')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->label('Nom')->required()->maxLength(255),

                Forms\Components\TextInput::make('badge')
                    ->label('Badge (optionnel, ex : Populaire)')
                    ->maxLength(50),

                Forms\Components\TextInput::make('price')
                    ->label('Prix')->required()->numeric()
                    ->prefix('FCFA')->minValue(0),

                Forms\Components\TextInput::make('price_note')
                    ->label('Note de prix (ex : / mois)')->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Description')->columnSpanFull(),

                Forms\Components\Toggle::make('featured')->label('Mis en avant (carte dorée)'),

                Forms\Components\TagsInput::make('features')
                    ->label('Caractéristiques (Entrée après chaque)')
                    ->columnSpanFull(),
                    Forms\Components\Select::make('level')->label('Niveau d\'accès')->required()
    ->helperText('1 = Basique · 2 = Standard · 3 = Premium · 4 = Entreprise')
    ->options([1 => '1 — Socle', 2 => '2 — Intermédiaire', 3 => '3 — Avancé', 4 => '4 — Maximal'])
    ->default(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Formule')->searchable()->weight('bold'),

                Tables\Columns\TextColumn::make('badge')->label('Badge')->placeholder('—'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->formatStateUsing(fn ($state) => number_format((int) $state, 0, ',', ' ') . ' FCFA')
                    ->sortable(),

                Tables\Columns\IconColumn::make('featured')->label('Une')->boolean(),

                Tables\Columns\TextColumn::make('features')
                    ->label('Caractéristiques')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' élément(s)' : '—'),
            ])
            ->defaultSort('price')
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
            'index' => Pages\ListPricingPlans::route('/'),
            'create' => Pages\CreatePricingPlan::route('/create'),
            'edit' => Pages\EditPricingPlan::route('/{record}/edit'),
        ];
    }
}