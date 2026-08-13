<?php

namespace App\Domain\Genplan;

use Database\Factories\PlotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plot extends Model
{
    /** @use HasFactory<PlotFactory> */
    use HasFactory;

    protected $fillable = [
        'quarter_id', 'number', 'slug', 'area', 'price', 'price_per_sotka', 'status',
        'polygon_data', 'marker_x', 'marker_y', 'description', 'image', 'attributes', 'is_visible',
    ];

    protected static function booted(): void
    {
        static::saving(function (Plot $plot): void {
            $geometry = app(NormalizedGeometry::class);
            $plot->polygon_data = $geometry->polygon($plot->polygon_data, 'polygon_data', true);
            $plot->marker_x = $geometry->coordinate($plot->marker_x, 'marker_x');
            $plot->marker_y = $geometry->coordinate($plot->marker_y, 'marker_y');
        });
    }

    protected function casts(): array
    {
        return [
            'area' => 'decimal:2',
            'price' => 'decimal:2',
            'price_per_sotka' => 'decimal:2',
            'status' => PlotStatus::class,
            'polygon_data' => 'array',
            'marker_x' => 'decimal:6',
            'marker_y' => 'decimal:6',
            'attributes' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('is_visible', true)
            ->where('status', '!=', PlotStatus::Hidden->value);
    }

    protected static function newFactory(): PlotFactory
    {
        return PlotFactory::new();
    }
}
