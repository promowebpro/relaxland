<?php

namespace App\Models;

use App\Domain\Blog\ArticleContentBlocks;
use App\Domain\Blog\BlogPostStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'cover_image',
        'content',
        'reading_time',
        'status',
        'published_at',
        'seo_title',
        'seo_description',
        'og_image',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post): void {
            $post->content = app(ArticleContentBlocks::class)->sanitize($post->content);
        });
    }

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'reading_time' => 'integer',
            'status' => BlogPostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_post_tags');
    }

    public function scopeInRubric(Builder $query, string $slug): Builder
    {
        return $query->where(function (Builder $query) use ($slug): void {
            $query->whereHas('tags', fn (Builder $tags) => $tags->active()->where('slug', $slug))
                ->orWhere(fn (Builder $legacy) => $legacy->whereDoesntHave('tags')
                    ->whereHas('category', fn (Builder $category) => $category->active()->where('slug', $slug)));
        });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('status', BlogPostStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function (Builder $query): void {
                $query->whereHas('tags', fn (Builder $tags) => $tags->active())
                    ->orWhere(fn (Builder $legacy) => $legacy->whereDoesntHave('tags')
                        ->whereHas('category', fn (Builder $category) => $category->active()));
            });
    }
}
