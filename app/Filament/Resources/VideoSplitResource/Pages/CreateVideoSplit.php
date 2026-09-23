<?php

namespace App\Filament\Resources\VideoSplitResource\Pages;

use App\Filament\Resources\VideoSplitResource;
use App\Jobs\SplitCourseVideo;
use Filament\Resources\Pages\CreateRecord;

class CreateVideoSplit extends CreateRecord
{
    protected static string $resource = VideoSplitResource::class;

    protected function afterCreate(): void
    {
        SplitCourseVideo::dispatch($this->record);
    }
}