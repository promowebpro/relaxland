<?php

namespace App\Filament\Resources\InfrastructurePoints\Pages;

use App\Filament\Resources\InfrastructurePoints\InfrastructurePointResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInfrastructurePoints extends ListRecords
{
    protected static string $resource = InfrastructurePointResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
