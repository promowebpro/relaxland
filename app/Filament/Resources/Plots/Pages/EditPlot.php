<?php

namespace App\Filament\Resources\Plots\Pages;

use App\Domain\Genplan\GenplanGeometryForm;
use App\Filament\Resources\Plots\PlotResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlot extends EditRecord
{
    protected static string $resource = PlotResource::class;

    private array $geometryData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return [...$data, ...app(GenplanGeometryForm::class)->plotData($this->record)];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->geometryData = app(GenplanGeometryForm::class)->extractPlot($data);

        return $data;
    }

    protected function afterSave(): void
    {
        app(GenplanGeometryForm::class)->syncPlot($this->record, $this->geometryData);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
