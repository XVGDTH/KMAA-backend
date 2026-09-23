<?php

namespace App\Filament\Resources\TeamSettingResource\Pages;

use App\Filament\Resources\TeamSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeamSettings extends ListRecords
{
    protected static string $resource = TeamSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
