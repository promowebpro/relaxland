<?php

namespace App\Filament\Resources\SurroundingPlaces\Pages;

use App\Filament\Resources\SurroundingPlaces\SurroundingPlaceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSurroundingPlace extends EditRecord
{
    protected static string $resource = SurroundingPlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
