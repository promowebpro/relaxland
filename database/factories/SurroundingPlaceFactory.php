<?php

namespace Database\Factories;

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
            'name' => fake()->company(),
            'category' => SurroundingCategory::Shopping,
            'latitude' => '55.5067000',
            'longitude' => '36.0215000',
            'description' => fake()->sentence(),
            'external_url' => fake()->url(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
