<?php

namespace App\Domain\Genplan;

use Illuminate\Validation\ValidationException;

class NormalizedGeometry
{
    public const SVG_SIZE = 1000;

    public function coordinate(mixed $value, string $field, bool $nullable = true): ?float
    {
        if ($nullable && ($value === null || $value === '')) {
            return null;
        }

        if (! is_numeric($value) || ! is_finite((float) $value)) {
            $this->fail($field, 'Координата должна быть числом от 0 до 1.');
        }

        $coordinate = (float) $value;

        if ($coordinate < 0 || $coordinate > 1) {
            $this->fail($field, 'Координата должна находиться в диапазоне от 0 до 1.');
        }

        return round($coordinate, 6) ?: 0.0;
    }

    /** @return array<int, array{x: float, y: float}>|null */
    public function polygon(mixed $value, string $field = 'polygon_data', bool $nullable = false): ?array
    {
        if ($nullable && ($value === null || $value === [] || $value === '')) {
            return null;
        }

        if (! is_array($value) || ! array_is_list($value) || count($value) < 3) {
            $this->fail($field, 'Полигон должен содержать минимум три точки.');
        }

        return array_map(function (mixed $point, int $index) use ($field): array {
            if (! is_array($point)
                || count($point) !== 2
                || array_diff(array_keys($point), ['x', 'y']) !== []
                || ! array_key_exists('x', $point)
                || ! array_key_exists('y', $point)) {
                $this->fail($field, 'Каждая точка должна содержать только координаты x и y.');
            }

            return [
                'x' => $this->coordinate($point['x'], "{$field}.{$index}.x", false),
                'y' => $this->coordinate($point['y'], "{$field}.{$index}.y", false),
            ];
        }, $value, array_keys($value));
    }

    /** @param array<int, array{x: float|int|string, y: float|int|string}> $polygon */
    public function svgPoints(array $polygon): string
    {
        $normalized = $this->polygon($polygon, 'polygon_data');

        return collect($normalized)
            ->map(fn (array $point): string => $this->formatSvgNumber($point['x'] * self::SVG_SIZE).','.$this->formatSvgNumber($point['y'] * self::SVG_SIZE))
            ->implode(' ');
    }

    private function formatSvgNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
