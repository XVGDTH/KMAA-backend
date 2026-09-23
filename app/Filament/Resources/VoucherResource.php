<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Bons / Vouchers';

    protected static ?string $modelLabel = 'Bon';

    protected static ?string $pluralModelLabel = 'Bons';


    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('code')
                ->label('Code du bon')
                ->disabled()
                ->dehydrated(false) // ne pas écraser la génération auto
                ->placeholder('Généré automatiquement (KMAA...)')
                ->helperText('Le code sera généré automatiquement à la création.'),

            Forms\Components\Select::make('course_id')
                ->label('Cours concerné')
                ->relationship('course', 'title')
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('Laissez vide = valable pour tous les cours.'),

            Forms\Components\TextInput::make('max_uses')
                ->label('Nombre max d’utilisations')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1),

            Forms\Components\TextInput::make('used_count')
                ->label('Déjà utilisé')
                ->numeric()
                ->default(0)
                ->disabled()
                ->dehydrated(),

            Forms\Components\DateTimePicker::make('expires_at')
                ->label('Date d’expiration')
                ->nullable()
                ->helperText('Laissez vide = pas d’expiration.'),

            Forms\Components\Toggle::make('active')
                ->label('Actif')
                ->default(true)
                ->required(),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Cours')
                    ->placeholder('Tous les cours')
                    ->sortable(),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Utilisations')
                    ->formatStateUsing(fn ($record) => $record->used_count . ' / ' . $record->max_uses)
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expire le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Jamais')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Actif'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}