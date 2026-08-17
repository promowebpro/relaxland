<?php

namespace App\Domain\Genplan;

use Database\Factories\SurroundingPlaceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class SurroundingPlace extends Model
{
    /** @use HasFactory<SurroundingPlaceFactory> */
    use HasFactory;

    protected $fillable = [
        'genplan_id', 'name', 'slug', 'category', 'latitude', 'longitude', 'description', 'image',
        'external_url', 'sort_order', 'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (SurroundingPlace $place): void {
            $attributes = $place->getAttributes();

            if (! is_string($attributes['slug'] ?? null)
                || preg_match('/\A[a-z0-9]+(?:[-_][a-z0-9]+)*\z/D', $attributes['slug']) !== 1) {
                throw ValidationException::withMessages([
                    'slug' => 'Публичный slug должен содержать только строчные латинские буквы, цифры, дефис или подчёркивание.',
                ]);
            }

            $point = new GeographicPoint(
                (string) ($attributes['latitude'] ?? ''),
                (string) ($attributes['longitude'] ?? ''),
            );

            $place->setAttribute('latitude', $point->latitude);
            $place->setAttribute('longitude', $point->longitude);

            if (filled($place->external_url) && PublicMapLink::https($place->external_url) === null) {
                throw ValidationException::withMessages([
                    'external_url' => 'Внешняя ссылка должна быть безопасным HTTPS URL.',
                ]);
            }

            if (filled($place->image)
                && (! is_string($place->image)
                    || ! str_starts_with($place->image, 'surroundings/')
                    || str_contains($place->image, '..'))) {
                throw ValidationException::withMessages([
                    'image' => 'Изображение должно находиться в публичном каталоге окружения.',
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'category' => SurroundingCategory::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePubliclyVisible(Builder $query, Genplan $genplan): Builder
    {
        return $query
            ->whereBelongsTo($genplan)
            ->active()
            ->whereIn('category', array_column(SurroundingCategory::cases(), 'value'))
            ->whereBetween('latitude', ['-90', '90'])
            ->whereBetween('longitude', ['-180', '180']);
    }

    public function genplan(): BelongsTo
    {
        return $this->belongsTo(Genplan::class);
    }

    public function geographicPoint(): ?GeographicPoint
    {
        return GeographicPoint::tryFrom($this->latitude, $this->longitude);
    }

    public function safeExternalUrl(): ?string
    {
        return PublicMapLink::https($this->external_url);
    }

    public function safeImagePath(): ?string
    {
        return is_string($this->image)
            && str_starts_with($this->image, 'surroundings/')
            && ! str_contains($this->image, '..')
                ? $this->image
                : null;
    }

    protected static function newFactory(): SurroundingPlaceFactory
    {
        return SurroundingPlaceFactory::new();
    }
}
