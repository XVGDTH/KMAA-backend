<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Paiements';
    protected static ?string $modelLabel = 'Paiement';

    public static function getNavigationBadge(): ?string
    {
        $count = Payment::where('status', 'en_attente')->count();
        return $count > 0 ? (string) $count : null;
    }

    // ✅ Les paiements viennent du site : pas de création manuelle admin
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Paiement')->columns(2)->schema([
                Forms\Components\TextInput::make('user.name')->label('Apprenant')->disabled(),
                Forms\Components\TextInput::make('course.title')->label('Cours')->disabled(),
                Forms\Components\TextInput::make('amount')->label('Montant')->disabled()
                    ->formatStateUsing(fn ($state) => number_format((int) $state, 0, ',', ' ') . ' FCFA'),
                Forms\Components\TextInput::make('provider')->label('Opérateur')->disabled(),

                Forms\Components\TextInput::make('phone_number')->label('Téléphone')->disabled(),
                Forms\Components\TextInput::make('transaction_reference')->label('Référence')->disabled(),

                Forms\Components\Select::make('status')->label('Statut')->required()
                    ->options([
                        'en_attente' => 'En attente',
                        'paye' => 'Payé',
                        'rembourse' => 'Remboursé',
                        'echoue' => 'Échoué',
                    ])->native(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Apprenant')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('course.title')->label('Cours')->limit(35),

                Tables\Columns\TextColumn::make('pricingPlan.name')->label('Formule')->placeholder('—'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => number_format((int) $state, 0, ',', ' ') . ' FCFA')
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider')->label('Opérateur')->badge(),

                Tables\Columns\TextColumn::make('phone_number')->label('Tél')->placeholder('—'),

                Tables\Columns\TextColumn::make('status')->label('Statut')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paye' => 'success', 'en_attente' => 'warning',
                        'rembourse' => 'info', 'echoue' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'en_attente' => 'En attente', 'paye' => '✓ Payé',
                        'rembourse' => 'Remboursé', 'echoue' => 'Échoué',
                    }),

                Tables\Columns\TextColumn::make('created_at')->label('Le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Statut')->options([
                    'en_attente' => 'En attente', 'paye' => 'Payé',
                    'rembourse' => 'Remboursé', 'echoue' => 'Échoué',
                ]),
                Tables\Filters\SelectFilter::make('provider')->label('Opérateur')->options([
                    'orange' => 'Orange Money', 'moov' => 'Moov Money', 'coris' => 'Coris Money',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('valider')
                    ->label('✅')->tooltip('Marquer comme payé')->color('success')
                    ->visible(fn (Payment $r) => $r->status === 'en_attente')
                    ->action(fn (Payment $r) => $r->update(['status' => 'paye'])),

                Tables\Actions\Action::make('echouer')
                    ->label('❌')->tooltip('Marquer comme échoué')->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $r) => $r->status === 'en_attente')
                    ->action(fn (Payment $r) => $r->update(['status' => 'echoue'])),

                Tables\Actions\Action::make('rembourser')
                    ->label('↩')->tooltip('Rembourser')->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $r) => $r->status === 'paye')
                    ->action(fn (Payment $r) => $r->update(['status' => 'rembourse'])),

                Tables\Actions\EditAction::make()->label('Détails'),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}