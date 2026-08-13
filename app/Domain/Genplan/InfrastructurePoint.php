<?php

namespace App\Domain\Genplan;

use Database\Factories\InfrastructurePointFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfrastructurePoint extends Model
{
    /** @use HasFactory<InfrastructurePointFactory> */
    use HasFactory;

    protected $fillable = [
        'genplan_id', 'name', 'category', 'icon', 'image', 'description',
        'show_on_3d', 'show_on_2d', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => InfrastructureCategory::class,
            'show_on_3d' => 'boolean',
            'show_on_2d' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function genplan(): BelongsTo
    {
        return $this->belongsTo(Genplan::class);
    }

    public function geometries(): HasMany
    {
        return $this->hasMany(InfrastructurePointGeometry::class);
    }

    public function geometryFor(GenplanMode $mode): ?InfrastructurePointGeometry
    {
        if ($this->relationLoaded('geometries')) {
            return $this->geometries->first(fn (InfrastructurePointGeometry $geometry): bool => $geometry->mode === $mode);
        }

        return $this->geometries()->where('mode', $mode->value)->first();
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
