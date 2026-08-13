<?php

namespace App\Filament\Resources\InfrastructurePoints\Pages;

use App\Filament\Resources\InfrastructurePoints\InfrastructurePointResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInfrastructurePoint extends EditRecord
{
    protected static string $resource = InfrastructurePointResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
