<?php

namespace App\Domain\Genplan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfrastructurePointGeometry extends Model
{
    protected $fillable = ['infrastructure_point_id', 'mode', 'marker_x', 'marker_y'];

    protected static function booted(): void
    {
        static::saving(function (InfrastructurePointGeometry $geometry): void {
            $normalizer = app(NormalizedGeometry::class);
            $geometry->marker_x = $normalizer->coordinate($geometry->marker_x, 'marker_x', false);
            $geometry->marker_y = $normalizer->coordinate($geometry->marker_y, 'marker_y', false);
        });
    }

    protected function casts(): array
    {
        return [
            'mode' => GenplanMode::class,
            'marker_x' => 'decimal:6',
            'marker_y' => 'decimal:6',
        ];
    }

    public function infrastructurePoint(): BelongsTo
    {
        return $this->belongsTo(InfrastructurePoint::class);
    }
}
