<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterCampaignResource\Pages;
use App\Jobs\SendNewsletterCampaign;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsletterCampaignResource extends Resource
{
    protected static ?string $model = NewsletterCampaign::class;
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Campagnes Brief';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Édition du KMAA Brief')->schema([
                Forms\Components\TextInput::make('subject')
                    ->label('Objet de l\'email')->required()->columnSpanFull(),

                Forms\Components\TextInput::make('intro')
                    ->label('Accroche (phrase d\'intro)')->columnSpanFull(),

                Forms\Components\RichEditor::make('content')
                    ->label('Contenu de l\'édition')->required()->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('Objet')->searchable()->limit(40)->weight('bold'),

                Tables\Columns\TextColumn::make('status')->label('Statut')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'gray', 'sending' => 'warning', 'sent' => 'success',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'draft' => '📝 Brouillon', 'sending' => '⏳ Envoi...', 'sent' => '✅ Envoyée',
                    }),

                Tables\Columns\TextColumn::make('sent_count')->label('Envoyée à')
                    ->suffix(' abonnés')->placeholder('—'),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Envoyée le')->dateTime('d/m/Y H:i')->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // ✅ LE BOUTON MAGIQUE : envoi à tous les abonnés actifs
                Tables\Actions\Action::make('envoyer')
                    ->label('🚀 Envoyer')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (NewsletterCampaign $r) => $r->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Envoyer cette édition ?')
                    ->modalDescription(function (NewsletterCampaign $record) {
                        $count = NewsletterSubscriber::where('status', 'subscribed')->count();
                        return "L'édition « {$record->subject} » sera envoyée à {$count} abonné(s) actifs. Cette action est irréversible.";
                    })
                    ->action(function (NewsletterCampaign $record) {
                        SendNewsletterCampaign::dispatch($record);
                    }),

                Tables\Actions\EditAction::make()->visible(
                    fn (NewsletterCampaign $r) => $r->status === 'draft'   // ✏️ édition bloquée après envoi
                ),
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
            'index' => Pages\ListNewsletterCampaigns::route('/'),
            'create' => Pages\CreateNewsletterCampaigns::route('/create'),
            'edit' => Pages\EditNewsletterCampaigns::route('/{record}/edit'),
        ];
    }
}