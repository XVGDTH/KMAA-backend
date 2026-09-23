<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['image'] = $this->resolveImagePath();

        unset($data['image_source'], $data['image_upload'], $data['image_url']);

        return $data;
    }

    private function resolveImagePath(): ?string
    {
        $state = $this->form->getState();

        // Priorité : fichier uploadé
        $upload = $this->form->getState()['image_upload']
            ?? $this->data['image_upload']
            ?? null;

        // Avec dehydrated(false), récupérer via les composants :
        $raw = $this->form->getRawState();

        if (($raw['image_source'] ?? 'url') === 'upload') {
            $file = $raw['image_upload'] ?? null;
            if (is_array($file)) {
                $file = $file[0] ?? null;
            }
            return $file ?: null;
        }

        $url = $raw['image_url'] ?? null;

        return $url ?: null;
    }
}