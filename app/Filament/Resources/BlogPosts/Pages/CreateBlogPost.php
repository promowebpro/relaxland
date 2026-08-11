<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Domain\Blog\BlogPostStatus;
use App\Domain\Users\Enums\PermissionName;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! auth()->user()?->can(PermissionName::ContentPublish->value)) {
            $data['status'] = BlogPostStatus::Draft->value;
            $data['published_at'] = null;
        }

        return $data;
    }
}
