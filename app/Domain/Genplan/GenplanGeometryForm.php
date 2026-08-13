<?php

namespace App\Domain\Genplan;

class GenplanGeometryForm
{
    public function quarterData(Quarter $quarter): array
    {
        $data = [];

        foreach (GenplanMode::cases() as $mode) {
            $geometry = $quarter->geometryFor($mode);
            $prefix = "geometry_{$mode->value}";
            $data["{$prefix}_polygon_data"] = $geometry?->polygon_data;
            $data["{$prefix}_label_x"] = $geometry?->label_x;
            $data["{$prefix}_label_y"] = $geometry?->label_y;
        }

        return $data;
    }

    public function plotData(Plot $plot): array
    {
        $data = [];

        foreach (GenplanMode::cases() as $mode) {
            $geometry = $plot->geometryFor($mode);
            $prefix = "geometry_{$mode->value}";
            $data["{$prefix}_polygon_data"] = $geometry?->polygon_data;
            $data["{$prefix}_marker_x"] = $geometry?->marker_x;
            $data["{$prefix}_marker_y"] = $geometry?->marker_y;
        }

        return $data;
    }

    public function infrastructureData(InfrastructurePoint $point): array
    {
        $data = [];

        foreach (GenplanMode::cases() as $mode) {
            $geometry = $point->geometryFor($mode);
            $prefix = "geometry_{$mode->value}";
            $data["{$prefix}_marker_x"] = $geometry?->marker_x;
            $data["{$prefix}_marker_y"] = $geometry?->marker_y;
        }

        return $data;
    }

    public function extractQuarter(array &$data): array
    {
        return $this->extract($data, ['polygon_data', 'label_x', 'label_y']);
    }

    public function extractPlot(array &$data): array
    {
        return $this->extract($data, ['polygon_data', 'marker_x', 'marker_y']);
    }

    public function extractInfrastructure(array &$data): array
    {
        return $this->extract($data, ['marker_x', 'marker_y']);
    }

    public function syncQuarter(Quarter $quarter, array $geometries): void
    {
        foreach (GenplanMode::cases() as $mode) {
            $values = $geometries[$mode->value];
            $polygon = $values['polygon_data'] ?? null;

            if (! $polygon) {
                $quarter->geometries()->where('mode', $mode->value)->delete();

                continue;
            }

            $quarter->geometries()->updateOrCreate(['mode' => $mode->value], $values);
        }
    }

    public function syncPlot(Plot $plot, array $geometries): void
    {
        foreach (GenplanMode::cases() as $mode) {
            $values = $geometries[$mode->value];
            $hasGeometry = ($values['polygon_data'] ?? null)
                || ($values['marker_x'] ?? null) !== null
                || ($values['marker_y'] ?? null) !== null;

            if (! $hasGeometry) {
                $plot->geometries()->where('mode', $mode->value)->delete();

                continue;
            }

            $plot->geometries()->updateOrCreate(['mode' => $mode->value], $values);
        }
    }

    public function syncInfrastructure(InfrastructurePoint $point, array $geometries): void
    {
        foreach (GenplanMode::cases() as $mode) {
            $values = $geometries[$mode->value];
            $hasGeometry = ($values['marker_x'] ?? null) !== null || ($values['marker_y'] ?? null) !== null;

            if (! $hasGeometry) {
                $point->geometries()->where('mode', $mode->value)->delete();

                continue;
            }

            $point->geometries()->updateOrCreate(['mode' => $mode->value], $values);
        }
    }

    private function extract(array &$data, array $fields): array
    {
        $geometries = [];

        foreach (GenplanMode::cases() as $mode) {
            foreach ($fields as $field) {
                $key = "geometry_{$mode->value}_{$field}";
                $geometries[$mode->value][$field] = $data[$key] ?? null;
                unset($data[$key]);
            }
        }

        return $geometries;
    }
}
