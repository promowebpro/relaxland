<?php

namespace App\Filament\Resources\Stories\Pages;

use App\Domain\Users\Enums\PermissionName;
use App\Filament\Resources\Stories\StoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStory extends CreateRecord
{
    protected static string $resource = StoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! auth()->user()?->can(PermissionName::ContentPublish->value)) {
            $data['is_active'] = false;
        }

        return $data;
    }
}
