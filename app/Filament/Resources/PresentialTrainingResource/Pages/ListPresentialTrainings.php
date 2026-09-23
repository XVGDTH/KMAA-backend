<?php

namespace App\Filament\Resources\PresentialTrainingResource\Pages;

use App\Filament\Resources\PresentialTrainingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPresentialTrainings extends ListRecords
{
    protected static string $resource = PresentialTrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
