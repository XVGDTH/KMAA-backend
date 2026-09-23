<?php

namespace App\Filament\Resources\ForumThreadResource\Pages;

use App\Filament\Resources\ForumThreadResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateForumThread extends CreateRecord
{
    protected static string $resource = ForumThreadResource::class;
}
