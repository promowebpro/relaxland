<?php

namespace App\Domain\Genplan;

use Database\Factories\InfrastructurePointFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfrastructurePoint extends Model
{
    /** @use HasFactory<InfrastructurePointFactory> */
    use HasFactory;

    protected $fillable = [
        'genplan_id', 'name', 'category', 'icon', 'image', 'description', 'marker_x', 'marker_y',
        'show_on_3d', 'show_on_2d', 'sort_order', 'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (InfrastructurePoint $point): void {
            $geometry = app(NormalizedGeometry::class);
            $point->marker_x = $geometry->coordinate($point->marker_x, 'marker_x', false);
            $point->marker_y = $geometry->coordinate($point->marker_y, 'marker_y', false);
        });
    }

    protected function casts(): array
    {
        return [
            'category' => InfrastructureCategory::class,
            'marker_x' => 'decimal:6',
            'marker_y' => 'decimal:6',
            'show_on_3d' => 'boolean',
            'show_on_2d' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function genplan(): BelongsTo
    {
        return $this->belongsTo(Genplan::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory(): InfrastructurePointFactory
    {
        return InfrastructurePointFactory::new();
    }
}
