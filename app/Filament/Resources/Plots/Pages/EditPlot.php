<?php

namespace App\Filament\Resources\Plots\Pages;

use App\Filament\Resources\Plots\PlotResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlot extends EditRecord
{
    protected static string $resource = PlotResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
