<?php

namespace App\Filament\Resources\Quarters\Pages;

use App\Domain\Genplan\GenplanGeometryForm;
use App\Filament\Resources\Quarters\QuarterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuarter extends CreateRecord
{
    protected static string $resource = QuarterResource::class;

    private array $geometryData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->geometryData = app(GenplanGeometryForm::class)->extractQuarter($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(GenplanGeometryForm::class)->syncQuarter($this->record, $this->geometryData);
    }
}
