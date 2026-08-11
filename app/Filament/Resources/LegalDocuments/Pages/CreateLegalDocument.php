<?php

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Domain\Users\Enums\PermissionName;
use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalDocument extends CreateRecord
{
    protected static string $resource = LegalDocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! auth()->user()?->can(PermissionName::ContentPublish->value)) {
            $data['is_active'] = false;
            $data['published_at'] = null;
        }

        return $data;
    }
}
