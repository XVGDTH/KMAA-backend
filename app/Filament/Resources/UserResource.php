<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\LoginAttemptService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Utilisateurs';
    protected static ?string $modelLabel = 'Utilisateur';
    protected static ?string $pluralModelLabel = 'Utilisateurs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // =====================================================
                // INFORMATIONS DU COMPTE
                // =====================================================

                Forms\Components\Section::make('Informations du compte')
                    ->schema([

                        Forms\Components\TextInput::make('name')
                            ->label('Nom complet')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Adresse email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255)
                            ->default(null),

                    ])
                    ->columns(2),

                // =====================================================
                // PROFIL
                // =====================================================

                Forms\Components\Section::make('Profil')
                    ->schema([

                        Forms\Components\TextInput::make('avatar')
                            ->label('Avatar')
                            ->maxLength(255)
                            ->default(null),

                        Forms\Components\Textarea::make('bio')
                            ->label('Biographie')
                            ->rows(4)
                            ->maxLength(1000)
                            ->default(null),

                    ])
                    ->columns(1),

                // =====================================================
                // SÉCURITÉ
                // =====================================================

                Forms\Components\Section::make('Sécurité')
                    ->schema([

                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(
                                fn ($state) => filled($state) ? bcrypt($state) : null
                            )
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context) => $context === 'create')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Compte actif')
                            ->default(true)
                            ->required(),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email vérifié le'),

                    ])
                    ->columns(2),

                // =====================================================
                // RÔLE
                // =====================================================

                Forms\Components\Section::make('Rôle et permissions')
                    ->schema([

                        Forms\Components\Select::make('roles')
                            ->label('Rôle(s)')
                            ->relationship(name: 'roles', titleAttribute: 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->native(false),

                    ]),

                // =====================================================
                // 🆕 STATUT DE SÉCURITÉ (lecture seule)
                // =====================================================

                Forms\Components\Section::make('🔒 Statut de sécurité')
                    ->description('Informations sur les tentatives de connexion')
                    ->schema([

                        Forms\Components\Placeholder::make('failed_login_attempts_display')
                            ->label('Tentatives échouées')
                            ->content(fn (?User $record) =>
                                $record
                                    ? $record->failed_login_attempts . ' / ' . LoginAttemptService::MAX_ATTEMPTS
                                    : '—'
                            ),

                        Forms\Components\Placeholder::make('locked_until_display')
                            ->label('Bloqué jusqu\'à')
                            ->content(fn (?User $record) =>
                                $record?->locked_until
                                    ? Carbon::parse($record->locked_until)->format('d/m/Y H:i')
                                    : '— Non bloqué —'
                            ),

                        Forms\Components\Placeholder::make('last_failed_login_at_display')
                            ->label('Dernière tentative échouée')
                            ->content(fn (?User $record) =>
                                $record?->last_failed_login_at
                                    ? Carbon::parse($record->last_failed_login_at)->format('d/m/Y H:i')
                                    : '—'
                            ),

                        Forms\Components\Placeholder::make('last_failed_login_ip_display')
                            ->label('IP de la dernière tentative')
                            ->content(fn (?User $record) =>
                                $record?->last_failed_login_ip ?? '—'
                            ),

                    ])
                    ->columns(2)
                    ->visible(fn (?User $record) =>
                        $record && (
                            $record->failed_login_attempts > 0
                            || $record->locked_until !== null
                        )
                    ),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôle(s)')
                    ->badge()
                    ->separator(',')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                // 🆕 Tentatives échouées
                Tables\Columns\TextColumn::make('failed_login_attempts')
                    ->label('Tentatives')
                    ->badge()
                    ->alignCenter()
                    ->color(fn ($state) =>
                        $state >= LoginAttemptService::MAX_ATTEMPTS
                            ? 'danger'
                            : ($state > 0 ? 'warning' : 'gray')
                    )
                    ->default(0),

                // 🆕 Statut de blocage
                Tables\Columns\IconColumn::make('locked_until')
                    ->label('Bloqué')
                    ->getStateUsing(function (User $record) {
                        if (! $record->locked_until) {
                            return false;
                        }

                        return Carbon::parse($record->locked_until)->isFuture();
                    })
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Email vérifié')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('roles')
                    ->label('Rôle')
                    ->relationship('roles', 'name')
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Compte actif'),

                // 🆕 Filtre "Comptes bloqués"
                Tables\Filters\Filter::make('locked')
                    ->label('🔒 Comptes bloqués')
                    ->query(fn ($query) =>
                        $query->whereNotNull('locked_until')
                               ->where('locked_until', '>', now())
                    )
                    ->toggle(),

                // 🆕 Filtre "Comptes avec tentatives échouées"
                Tables\Filters\Filter::make('has_failed_attempts')
                    ->label('⚠️ Tentatives échouées')
                    ->query(fn ($query) =>
                        $query->where('failed_login_attempts', '>', 0)
                    )
                    ->toggle(),

            ])

            ->actions([

                // 🆕 Action "Débloquer"
                Tables\Actions\Action::make('unlock')
                    ->label('Débloquer')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->visible(function (User $record) {
                        if (! $record->locked_until) {
                            return false;
                        }

                        return Carbon::parse($record->locked_until)->isFuture()
                            || $record->failed_login_attempts > 0;
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Débloquer ce compte ?')
                    ->modalDescription('Le compteur de tentatives échouées sera remis à zéro et le compte pourra se reconnecter immédiatement.')
                    ->modalSubmitActionLabel('Oui, débloquer')
                    ->action(function (User $record) {
                        app(LoginAttemptService::class)->unlock($record);

                        Notification::make()
                            ->title('✅ Compte débloqué')
                            ->body("{$record->name} peut maintenant se reconnecter.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),

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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}