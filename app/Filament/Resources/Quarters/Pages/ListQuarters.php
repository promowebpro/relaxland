<?php

namespace App\Filament\Resources\Quarters\Pages;

use App\Filament\Resources\Quarters\QuarterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuarters extends ListRecords
{
    protected static string $resource = QuarterResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
