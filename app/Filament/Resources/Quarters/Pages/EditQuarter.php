<?php

namespace App\Filament\Resources\Quarters\Pages;

use App\Domain\Genplan\GenplanGeometryForm;
use App\Filament\Resources\Quarters\QuarterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuarter extends EditRecord
{
    protected static string $resource = QuarterResource::class;

    private array $geometryData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return [...$data, ...app(GenplanGeometryForm::class)->quarterData($this->record)];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->geometryData = app(GenplanGeometryForm::class)->extractQuarter($data);

        return $data;
    }

    protected function afterSave(): void
    {
        app(GenplanGeometryForm::class)->syncQuarter($this->record, $this->geometryData);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
