<?php

namespace App\Filament\Resources\Stories\Pages;

use App\Domain\Users\Enums\PermissionName;
use App\Filament\Resources\Stories\StoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStory extends EditRecord
{
    protected static string $resource = StoryResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! auth()->user()?->can(PermissionName::ContentPublish->value)) {
            $data['is_active'] = $this->getRecord()->is_active;
        }

        return $data;
    }
}
