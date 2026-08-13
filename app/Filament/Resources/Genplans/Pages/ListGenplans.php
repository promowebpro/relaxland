<?php

namespace App\Filament\Resources\Genplans\Pages;

use App\Filament\Resources\Genplans\GenplanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGenplans extends ListRecords
{
    protected static string $resource = GenplanResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
