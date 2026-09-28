<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Domain\Users\Enums\PermissionName;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected array $tagIds = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['tag_ids'] = $this->record->tags()->pluck('blog_categories.id')->all() ?: [$data['category_id']];

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->tags()->sync($this->tagIds);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->tagIds = $data['tag_ids'];
        $data['category_id'] = $this->tagIds[0];
        unset($data['tag_ids']);
        if (! auth()->user()?->can(PermissionName::ContentPublish->value)) {
            $data['status'] = $this->getRecord()->status->value;
            $data['published_at'] = $this->getRecord()->published_at;
        }

        return $data;
    }
}
