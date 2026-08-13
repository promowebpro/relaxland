<?php

namespace App\Filament\Resources\InfrastructurePoints\Pages;

use App\Domain\Genplan\GenplanGeometryForm;
use App\Filament\Resources\InfrastructurePoints\InfrastructurePointResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInfrastructurePoint extends EditRecord
{
    protected static string $resource = InfrastructurePointResource::class;

    private array $geometryData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return [...$data, ...app(GenplanGeometryForm::class)->infrastructureData($this->record)];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->geometryData = app(GenplanGeometryForm::class)->extractInfrastructure($data);

        return $data;
    }

    protected function afterSave(): void
    {
        app(GenplanGeometryForm::class)->syncInfrastructure($this->record, $this->geometryData);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
