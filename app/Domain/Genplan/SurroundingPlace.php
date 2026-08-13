<?php

namespace App\Domain\Genplan;

use Database\Factories\SurroundingPlaceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurroundingPlace extends Model
{
    /** @use HasFactory<SurroundingPlaceFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'category', 'latitude', 'longitude', 'description', 'external_url', 'sort_order', 'is_active',
    ];

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

    protected static function newFactory(): SurroundingPlaceFactory
    {
        return SurroundingPlaceFactory::new();
    }
}
