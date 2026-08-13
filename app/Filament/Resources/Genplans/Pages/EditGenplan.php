<?php

namespace App\Filament\Resources\Genplans\Pages;

use App\Filament\Resources\Genplans\GenplanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGenplan extends EditRecord
{
    protected static string $resource = GenplanResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
