<?php

namespace App\Filament\Resources\TeamSettingResource\Pages;

use App\Filament\Resources\TeamSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeamSetting extends EditRecord
{
    protected static string $resource = TeamSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
