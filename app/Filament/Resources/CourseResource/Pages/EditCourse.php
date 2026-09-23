<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $image = $data['image'] ?? '';

        if (is_string($image) && str_starts_with($image, 'http')) {
            $data['image_source'] = 'url';
            $data['image_url_input'] = $image;
        } else {
            $data['image_source'] = 'upload';
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($this->data['image_source'] ?? '') === 'url') {
            $data['image'] = $this->data['image_url_input'] ?? null;
        }

        return $data;
    }
}