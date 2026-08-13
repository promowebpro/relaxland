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
        'genplan_id', 'name', 'slug', 'description', 'status', 'polygon_data',
        'label_x', 'label_y', 'sort_order', 'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (Quarter $quarter): void {
            $geometry = app(NormalizedGeometry::class);
            $quarter->polygon_data = $geometry->polygon($quarter->polygon_data);
            $quarter->label_x = $geometry->coordinate($quarter->label_x, 'label_x');
            $quarter->label_y = $geometry->coordinate($quarter->label_y, 'label_y');
        });
    }

    protected function casts(): array
    {
        return [
            'status' => QuarterStatus::class,
            'polygon_data' => 'array',
            'label_x' => 'decimal:6',
            'label_y' => 'decimal:6',
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
