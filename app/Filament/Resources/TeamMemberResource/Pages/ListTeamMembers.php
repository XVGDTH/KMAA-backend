<?php

namespace App\Filament\Resources\TeamMemberResource\Pages;

use App\Filament\Resources\TeamMemberResource;
use App\Filament\Resources\TeamSettingResource;
use App\Models\TeamSetting;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeamMembers extends ListRecords
{
    protected static string $resource = TeamMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un membre'),

            Actions\Action::make('textesPage')
                ->label('Textes de la page')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->url(function () {
                    $setting = TeamSetting::first();

                    return $setting
                        ? TeamSettingResource::getUrl('edit', ['record' => $setting])
                        : TeamSettingResource::getUrl('create');
                }),
        ];
    }
}