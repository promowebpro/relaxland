<?php

namespace Database\Factories;

use App\Domain\Content\LegalDocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LegalDocumentFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'type' => LegalDocumentType::Other,
            'content' => '<p>'.e(fake()->paragraph()).'</p>',
            'file_path' => null,
            'version' => null,
            'published_at' => now(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
