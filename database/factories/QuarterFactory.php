<?php

namespace Database\Factories;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\GenplanMode;
use App\Domain\Genplan\Quarter;
use App\Domain\Genplan\QuarterStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Quarter> */
class QuarterFactory extends Factory
{
    protected $model = Quarter::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Quarter $quarter): void {
            $quarter->geometries()->create([
                'mode' => GenplanMode::ThreeD,
                'polygon_data' => [
                    ['x' => 0.1, 'y' => 0.1],
                    ['x' => 0.4, 'y' => 0.1],
                    ['x' => 0.4, 'y' => 0.4],
                    ['x' => 0.1, 'y' => 0.4],
                ],
                'label_x' => 0.25,
                'label_y' => 0.25,
            ]);
        });
    }

    public function definition(): array
    {
        return [
            'genplan_id' => Genplan::factory(),
            'name' => 'Тестовый квартал '.fake()->unique()->numberBetween(1, 9999),
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'status' => QuarterStatus::Available,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
