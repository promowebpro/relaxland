<?php

namespace Database\Factories;

use App\Domain\Blog\BlogPostStatus;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<BlogPost> */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return [
            'category_id' => BlogCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'excerpt' => fake()->sentence(14),
            'cover_image' => null,
            'content' => [
                ['type' => 'heading', 'data' => ['text' => fake()->sentence(4), 'level' => 2]],
                ['type' => 'rich_text', 'data' => ['html' => '<p>'.fake()->paragraph().'</p>']],
            ],
            'reading_time' => fake()->numberBetween(2, 12),
            'status' => BlogPostStatus::Published,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'seo_title' => null,
            'seo_description' => null,
            'og_image' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => BlogPostStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function future(): static
    {
        return $this->state(fn (): array => [
            'status' => BlogPostStatus::Published,
            'published_at' => now()->addDay(),
        ]);
    }
}
