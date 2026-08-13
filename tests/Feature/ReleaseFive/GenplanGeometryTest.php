<?php

namespace Tests\Feature\ReleaseFive;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\InfrastructurePoint;
use App\Domain\Genplan\NormalizedGeometry;
use App\Domain\Genplan\Plot;
use App\Domain\Genplan\Quarter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ValueError;

class GenplanGeometryTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalized_coordinates_are_accepted_rounded_and_rendered_deterministically(): void
    {
        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create([
            'genplan_id' => $genplan->id,
            'polygon_data' => [
                ['x' => '0.0000004', 'y' => '0'],
                ['x' => '0.5000004', 'y' => '0.25'],
                ['x' => '1', 'y' => '1'],
            ],
            'label_x' => '0.3333334',
            'label_y' => '1',
        ]);

        $this->assertEquals([
            ['x' => 0.0, 'y' => 0.0],
            ['x' => 0.5, 'y' => 0.25],
            ['x' => 1.0, 'y' => 1.0],
        ], $quarter->polygon_data);
        $this->assertSame('0.333333', $quarter->label_x);
        $this->assertSame('0,0 500,250 1000,1000', app(NormalizedGeometry::class)->svgPoints($quarter->polygon_data));
    }

    public function test_polygon_rejects_too_few_points_unknown_keys_and_out_of_range_coordinates(): void
    {
        $geometry = app(NormalizedGeometry::class);

        foreach ([
            [['x' => 0, 'y' => 0], ['x' => 1, 'y' => 1]],
            [['x' => 0, 'y' => 0], ['x' => 1, 'y' => 0], ['x' => 1, 'y' => 1, 'z' => 2]],
            [['x' => -0.01, 'y' => 0], ['x' => 1, 'y' => 0], ['x' => 1, 'y' => 1]],
            [['x' => 0, 'y' => 0], ['x' => 1.01, 'y' => 0], ['x' => 1, 'y' => 1]],
        ] as $polygon) {
            try {
                $geometry->polygon($polygon);
                $this->fail('Malformed geometry was accepted.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
                $this->assertStringStartsWith('polygon_data', array_key_first($exception->errors()));
            }
        }
    }

    public function test_model_layer_rejects_invalid_marker_coordinates(): void
    {
        $this->expectException(ValidationException::class);

        InfrastructurePoint::factory()->create([
            'marker_x' => 1.001,
            'marker_y' => 0.5,
        ]);
    }

    public function test_plot_money_uses_exact_decimal_precision(): void
    {
        $plot = Plot::factory()->create([
            'area' => '12.345',
            'price' => '1234567.895',
            'price_per_sotka' => '100000.005',
        ])->refresh();

        $this->assertSame('12.35', $plot->area);
        $this->assertSame('1234567.90', $plot->price);
        $this->assertSame('100000.01', $plot->price_per_sotka);
    }

    public function test_status_enums_reject_arbitrary_values(): void
    {
        $this->expectException(ValueError::class);

        Quarter::factory()->make(['status' => 'invented-status']);
    }
}
