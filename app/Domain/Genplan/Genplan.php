<?php

namespace App\Domain\Genplan;

use Database\Factories\GenplanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Genplan extends Model
{
    /** @use HasFactory<GenplanFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'image_3d', 'image_2d', 'mobile_image_3d', 'mobile_image_2d',
        'mobile_image_3d_is_compatible', 'mobile_image_2d_is_compatible',
        'original_width', 'original_height', 'is_active', 'settings',
    ];

    protected static function booted(): void
    {
        static::saving(function (Genplan $genplan): void {
            $errors = [];

            foreach (GenplanMode::cases() as $mode) {
                $imageField = "mobile_image_{$mode->value}";
                $guardField = "mobile_image_{$mode->value}_is_compatible";

                if ($genplan->{$imageField} && ! $genplan->{$guardField}) {
                    $errors[$guardField] = "Подтвердите, что mobile {$mode->label()} использует ту же проекцию и framing.";
                }
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'mobile_image_3d_is_compatible' => 'boolean',
            'mobile_image_2d_is_compatible' => 'boolean',
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

    public function surroundingPlaces(): HasMany
    {
        return $this->hasMany(SurroundingPlace::class);
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
