<?php

namespace App\Domain\Genplan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuarterGeometry extends Model
{
    protected $fillable = ['quarter_id', 'mode', 'polygon_data', 'label_x', 'label_y'];

    protected static function booted(): void
    {
        static::saving(function (QuarterGeometry $geometry): void {
            $normalizer = app(NormalizedGeometry::class);
            $geometry->polygon_data = $normalizer->polygon($geometry->polygon_data);
            $geometry->label_x = $normalizer->coordinate($geometry->label_x, 'label_x');
            $geometry->label_y = $normalizer->coordinate($geometry->label_y, 'label_y');
        });
    }

    protected function casts(): array
    {
        return [
            'mode' => GenplanMode::class,
            'polygon_data' => 'array',
            'label_x' => 'decimal:6',
            'label_y' => 'decimal:6',
        ];
    }

    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }
}
