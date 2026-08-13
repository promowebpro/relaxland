<?php

namespace App\Filament\Resources\Quarters\Pages;

use App\Filament\Resources\Quarters\QuarterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuarter extends EditRecord
{
    protected static string $resource = QuarterResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
