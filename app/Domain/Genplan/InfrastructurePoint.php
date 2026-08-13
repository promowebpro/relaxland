<?php

namespace App\Domain\Genplan;

use Database\Factories\InfrastructurePointFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InfrastructurePoint extends Model
{
    /** @use HasFactory<InfrastructurePointFactory> */
    use HasFactory;

    protected $fillable = [
        'genplan_id', 'name', 'slug', 'category', 'icon', 'image', 'description',
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

    protected static function booted(): void
    {
        static::saving(function (self $point): void {
            if (filled($point->slug)) {
                return;
            }

            $base = Str::slug($point->name) ?: 'point';
            $candidate = $base;
            $suffix = 2;

            while (self::query()
                ->where('genplan_id', $point->genplan_id)
                ->where('slug', $candidate)
                ->when($point->exists, fn ($query) => $query->whereKeyNot($point->getKey()))
                ->exists()) {
                $candidate = "{$base}-{$suffix}";
                $suffix++;
            }

            $point->slug = $candidate;
        });
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
