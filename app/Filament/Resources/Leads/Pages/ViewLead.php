<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Domain\Users\Enums\PermissionName;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => auth()->user()?->can(PermissionName::LeadsUpdate->value) ?? false),
        ];
    }
}
