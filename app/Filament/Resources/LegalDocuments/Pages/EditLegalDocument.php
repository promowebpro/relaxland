<?php

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Domain\Users\Enums\PermissionName;
use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLegalDocument extends EditRecord
{
    protected static string $resource = LegalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! auth()->user()?->can(PermissionName::ContentPublish->value)) {
            $data['is_active'] = $this->getRecord()->is_active;
            $data['published_at'] = $this->getRecord()->published_at;
        }

        return $data;
    }
}
