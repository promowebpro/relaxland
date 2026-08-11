<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Domain\Users\Enums\PermissionName;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! auth()->user()?->can(PermissionName::ContentPublish->value)) {
            $data['status'] = $this->getRecord()->status->value;
            $data['published_at'] = $this->getRecord()->published_at;
        }

        return $data;
    }
}
