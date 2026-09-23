<?php

namespace App\Filament\Resources\ListJobApplicationsResource\Pages;

use App\Filament\Resources\ListJobApplicationsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditListJobApplications extends EditRecord
{
    protected static string $resource = ListJobApplicationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
