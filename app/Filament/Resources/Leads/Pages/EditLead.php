<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Domain\Leads\AssignableLeadManagers;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $managerId = filled($data['assigned_to'] ?? null) ? (int) $data['assigned_to'] : null;

        if (! app(AssignableLeadManagers::class)->contains($managerId)) {
            throw ValidationException::withMessages([
                'data.assigned_to' => 'Выбранный пользователь не может работать с заявками.',
            ]);
        }

        $data['assigned_to'] = $managerId;

        return $data;
    }
}
