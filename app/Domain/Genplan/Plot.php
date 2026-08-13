<?php

namespace App\Domain\Genplan;

use Database\Factories\PlotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plot extends Model
{
    /** @use HasFactory<PlotFactory> */
    use HasFactory;

    protected $fillable = [
        'quarter_id', 'number', 'slug', 'area', 'price', 'price_per_sotka', 'status',
        'description', 'image', 'attributes', 'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'area' => 'decimal:2',
            'price' => 'decimal:2',
            'price_per_sotka' => 'decimal:2',
            'status' => PlotStatus::class,
            'attributes' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }

    public function geometries(): HasMany
    {
        return $this->hasMany(PlotGeometry::class);
    }

    public function geometryFor(GenplanMode $mode): ?PlotGeometry
    {
        if ($this->relationLoaded('geometries')) {
            return $this->geometries->first(fn (PlotGeometry $geometry): bool => $geometry->mode === $mode);
        }

        return $this->geometries()->where('mode', $mode->value)->first();
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
