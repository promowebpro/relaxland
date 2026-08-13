<?php

namespace App\Filament\Resources\SurroundingPlaces\Pages;

use App\Filament\Resources\SurroundingPlaces\SurroundingPlaceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSurroundingPlaces extends ListRecords
{
    protected static string $resource = SurroundingPlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
