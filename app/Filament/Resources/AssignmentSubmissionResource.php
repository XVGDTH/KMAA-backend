<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentSubmissionResource\Pages;
use App\Models\AssignmentSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionResource extends Resource
{
    protected static ?string $model = AssignmentSubmission::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationLabel = 'Soumissions devoirs';
    protected static ?string $modelLabel = 'Soumission';

    public static function getNavigationBadge(): ?string
    {
        $count = AssignmentSubmission::where('status', 'soumis')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function canCreate(): bool
    {
        return false;   // créées par les apprenants uniquement
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Soumission')->columns(2)->schema([
                Forms\Components\TextInput::make('user.name')->label('Apprenant')->disabled(),
                Forms\Components\TextInput::make('sequence.title')->label('Séquence')->disabled(),
                Forms\Components\Textarea::make('comment')->label('Commentaire apprenant')->disabled()->columnSpanFull(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Devoir rendu')->disk('public')
                    ->downloadable()->openable()->disabled()->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Correction')->columns(2)->schema([
                Forms\Components\Select::make('status')->label('Statut')->required()
                    ->options([
                        'soumis' => 'Soumis',
                        'corrige' => 'Corrigé',
                        'retourne' => 'Retourné (à refaire)',
                    ])->native(false),

                Forms\Components\TextInput::make('grade')->label('Note /20')->numeric()->minValue(0)->maxValue(20),

                Forms\Components\Textarea::make('instructor_feedback')
                    ->label('Feedback personnalisé')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Apprenant')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('sequence.title')->label('Séquence')->limit(35),

                Tables\Columns\TextColumn::make('status')->label('Statut')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'soumis' => 'warning', 'corrige' => 'success', default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('grade')->label('Note')
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state}/20" : '—'),

                Tables\Columns\TextColumn::make('created_at')->label('Le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Statut')->options([
                    'soumis' => 'Soumis', 'corrige' => 'Corrigé', 'retourne' => 'Retourné',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('cv')
                    ->label('📄')->tooltip('Ouvrir le devoir')
                    ->url(fn (AssignmentSubmission $r) =>
                        $r->file_path ? asset('storage/' . $r->file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (AssignmentSubmission $r) => (bool) $r->file_path),

                Tables\Actions\EditAction::make()->label('Corriger'),
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
            'index' => Pages\ListAssignmentSubmissions::route('/'),
            'create' => Pages\CreateAssignmentSubmissions::route('/create'),
            'edit' => Pages\EditAssignmentSubmissions::route('/{record}/edit'),
        ];
    }
}