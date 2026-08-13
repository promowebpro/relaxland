<?php

namespace App\Domain\Genplan;

use Database\Factories\QuarterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quarter extends Model
{
    /** @use HasFactory<QuarterFactory> */
    use HasFactory;

    protected $fillable = [
        'genplan_id', 'name', 'slug', 'description', 'status', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuarterStatus::class,
            'is_active' => 'boolean',
        ];
    }

    public function genplan(): BelongsTo
    {
        return $this->belongsTo(Genplan::class);
    }

    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class);
    }

    public function geometries(): HasMany
    {
        return $this->hasMany(QuarterGeometry::class);
    }

    public function geometryFor(GenplanMode $mode): ?QuarterGeometry
    {
        if ($this->relationLoaded('geometries')) {
            return $this->geometries->first(fn (QuarterGeometry $geometry): bool => $geometry->mode === $mode);
        }

        return $this->geometries()->where('mode', $mode->value)->first();
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('status', '!=', QuarterStatus::Hidden->value);
    }

    protected static function newFactory(): QuarterFactory
    {
        return QuarterFactory::new();
    }
}
