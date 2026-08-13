<?php

namespace Database\Factories;

use App\Domain\Genplan\Genplan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Genplan> */
class GenplanFactory extends Factory
{
    protected $model = Genplan::class;

    public function definition(): array
    {
        $name = 'Тестовый генплан '.fake()->unique()->numberBetween(1, 9999);

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(2),
            'image_3d' => 'genplan/testing/plan-3d.webp',
            'image_2d' => 'genplan/testing/plan-2d.webp',
            'original_width' => 2000,
            'original_height' => 1400,
            'is_active' => true,
        ];
    }
}
