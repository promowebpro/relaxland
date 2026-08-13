<?php

namespace App\Domain\Genplan;

use Database\Factories\GenplanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Genplan extends Model
{
    /** @use HasFactory<GenplanFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'image_3d', 'image_2d', 'mobile_image_3d', 'mobile_image_2d',
        'original_width', 'original_height', 'is_active', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function quarters(): HasMany
    {
        return $this->hasMany(Quarter::class);
    }

    public function infrastructurePoints(): HasMany
    {
        return $this->hasMany(InfrastructurePoint::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory(): GenplanFactory
    {
        return GenplanFactory::new();
    }
}
