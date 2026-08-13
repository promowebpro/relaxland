<?php

namespace Database\Factories;

use App\Domain\Genplan\Plot;
use App\Domain\Genplan\PlotStatus;
use App\Domain\Genplan\Quarter;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Plot> */
class PlotFactory extends Factory
{
    protected $model = Plot::class;

    public function definition(): array
    {
        $number = (string) fake()->unique()->numberBetween(1, 9999);

        return [
            'quarter_id' => Quarter::factory(),
            'number' => $number,
            'slug' => 'plot-'.$number,
            'area' => '12.35',
            'price' => '1234567.89',
            'price_per_sotka' => '99999.99',
            'status' => PlotStatus::Available,
            'marker_x' => 0.2,
            'marker_y' => 0.2,
            'is_visible' => true,
        ];
    }
}
