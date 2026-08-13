<?php

namespace App\Domain\Genplan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class PlotGeometry extends Model
{
    protected $fillable = ['plot_id', 'mode', 'polygon_data', 'marker_x', 'marker_y'];

    protected static function booted(): void
    {
        static::saving(function (PlotGeometry $geometry): void {
            $normalizer = app(NormalizedGeometry::class);
            $geometry->polygon_data = $normalizer->polygon($geometry->polygon_data, 'polygon_data', true);
            $geometry->marker_x = $normalizer->coordinate($geometry->marker_x, 'marker_x');
            $geometry->marker_y = $normalizer->coordinate($geometry->marker_y, 'marker_y');

            if (($geometry->marker_x === null) !== ($geometry->marker_y === null)) {
                throw ValidationException::withMessages([
                    'marker_x' => 'Координаты маркера должны быть указаны парой.',
                    'marker_y' => 'Координаты маркера должны быть указаны парой.',
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'mode' => GenplanMode::class,
            'polygon_data' => 'array',
            'marker_x' => 'decimal:6',
            'marker_y' => 'decimal:6',
        ];
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }
}
