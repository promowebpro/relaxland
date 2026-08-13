<?php

namespace App\Filament\Resources\InfrastructurePoints\Pages;

use App\Domain\Genplan\GenplanGeometryForm;
use App\Filament\Resources\InfrastructurePoints\InfrastructurePointResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInfrastructurePoint extends CreateRecord
{
    protected static string $resource = InfrastructurePointResource::class;

    private array $geometryData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->geometryData = app(GenplanGeometryForm::class)->extractInfrastructure($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(GenplanGeometryForm::class)->syncInfrastructure($this->record, $this->geometryData);
    }
}
