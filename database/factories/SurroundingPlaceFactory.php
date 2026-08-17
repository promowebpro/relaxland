<?php

namespace Database\Factories;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\SurroundingCategory;
use App\Domain\Genplan\SurroundingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SurroundingPlace> */
class SurroundingPlaceFactory extends Factory
{
    protected $model = SurroundingPlace::class;

    public function definition(): array
    {
        return [
            'genplan_id' => Genplan::factory(),
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(2),
            'category' => SurroundingCategory::Shopping,
            'latitude' => '55.5067000',
            'longitude' => '36.0215000',
            'description' => fake()->sentence(),
            'image' => null,
            'external_url' => 'https://yandex.ru/maps/',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
