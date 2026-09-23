<?php

namespace App\Filament\Resources\VideoSplitResource\Pages;

use App\Filament\Resources\VideoSplitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVideoSplit extends EditRecord
{
    protected static string $resource = VideoSplitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
