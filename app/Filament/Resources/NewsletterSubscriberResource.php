<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationLabel = 'Newsletter';
    protected static ?string $modelLabel = 'Abonné newsletter';

    public static function getNavigationBadge(): ?string
    {
        $count = NewsletterSubscriber::where('status', 'subscribed')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Abonné')->schema([
                Forms\Components\TextInput::make('email')->label('Email')
                    ->email()->required()->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('name')->label('Nom (optionnel)'),

                Forms\Components\Select::make('status')->label('Statut')->required()
                    ->options([
                        'subscribed' => 'Abonné',
                        'unsubscribed' => 'Désinscrit',
                    ])
                    ->default('subscribed')->native(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')->searchable()->sortable()->copyable()->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')->badge()
                    ->color(fn (string $state) => $state === 'subscribed' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state) =>
                        $state === 'subscribed' ? '✓ Abonné' : '✕ Désinscrit'),

                Tables\Columns\TextColumn::make('subscribed_at')
                    ->label('Inscrit le')->dateTime('d/m/Y H:i')->sortable(),

                Tables\Columns\TextColumn::make('unsubscribed_at')
                    ->label('Désinscrit le')->dateTime('d/m/Y')->placeholder('—'),
            ])
            ->defaultSort('subscribed_at', 'desc')
            // ->filters([
            //     Tables\Filters\TernaryFilter::make('status')
            //         ->label('Statut')
            //         ->placeholder('Tous')
            //         ->trueLabel('Abonnés')
            //         ->falseLabel('Désinscrits')
            //         ->queries(
            //             subscribed: fn ($q) => $q->where('status', 'subscribed'),
            //             unsubscribed: fn ($q) => $q->where('status', 'unsubscribed'),
            //         ),
            // ])
                        ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'subscribed' => 'Abonnés',
                        'unsubscribed' => 'Désinscrits',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('desabonner')
                    ->label('Désinscrire')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (NewsletterSubscriber $r) => $r->status === 'subscribed')
                    ->action(fn (NewsletterSubscriber $r) => $r->update([
                        'status' => 'unsubscribed', 'unsubscribed_at' => now(),
                    ])),

                Tables\Actions\Action::make('reabonner')
                    ->label('Réabonner')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (NewsletterSubscriber $r) => $r->status === 'unsubscribed')
                    ->action(fn (NewsletterSubscriber $r) => $r->update([
                        'status' => 'subscribed', 'subscribed_at' => now(), 'unsubscribed_at' => null,
                    ])),

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
        // ⚠️ ADAPTE au dir réel (CreateNewsletterSubscriber / EditNewsletterSubscriber attendus)
        return [
            'index' => Pages\ListNewsletterSubscribers::route('/'),
            'create' => Pages\CreateNewsletterSubscriber::route('/create'),
            'edit' => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }
}