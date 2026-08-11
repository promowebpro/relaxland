<?php

namespace App\Filament\Resources\BlogCategories\Pages;

use App\Domain\Users\Enums\PermissionName;
use App\Filament\Resources\BlogCategories\BlogCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogCategory extends CreateRecord
{
    protected static string $resource = BlogCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! auth()->user()?->can(PermissionName::ContentPublish->value)) {
            $data['is_active'] = false;
        }

        return $data;
    }
}
