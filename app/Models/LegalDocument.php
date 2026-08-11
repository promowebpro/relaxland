<?php

namespace App\Models;

use App\Domain\Content\LegalContentSanitizer;
use App\Domain\Content\LegalDocumentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'content',
        'file_path',
        'version',
        'published_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => LegalDocumentType::class,
            'published_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected function content(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => app(LegalContentSanitizer::class)->sanitize($value),
        );
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }
}
