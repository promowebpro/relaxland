<?php

namespace Database\Factories;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\InfrastructureCategory;
use App\Domain\Genplan\InfrastructurePoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InfrastructurePoint> */
class InfrastructurePointFactory extends Factory
{
    protected $model = InfrastructurePoint::class;

    public function definition(): array
    {
        return [
            'genplan_id' => Genplan::factory(),
            'name' => fake()->sentence(2),
            'category' => InfrastructureCategory::Playground,
            'marker_x' => fake()->randomFloat(4, 0.05, 0.95),
            'marker_y' => fake()->randomFloat(4, 0.05, 0.95),
            'show_on_3d' => true,
            'show_on_2d' => true,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
