<?php

namespace App\Filament\Resources\Plots\Pages;

use App\Domain\Genplan\GenplanGeometryForm;
use App\Filament\Resources\Plots\PlotResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlot extends CreateRecord
{
    protected static string $resource = PlotResource::class;

    private array $geometryData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->geometryData = app(GenplanGeometryForm::class)->extractPlot($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(GenplanGeometryForm::class)->syncPlot($this->record, $this->geometryData);
    }
}
