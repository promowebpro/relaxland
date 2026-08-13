<?php

namespace Tests\Feature\ReleaseFive;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\GenplanMode;
use App\Domain\Genplan\GenplanPublicQuery;
use App\Domain\Genplan\InfrastructurePoint;
use App\Domain\Genplan\InfrastructurePointGeometry;
use App\Domain\Genplan\NormalizedGeometry;
use App\Domain\Genplan\Plot;
use App\Domain\Genplan\Quarter;
use App\Domain\Genplan\QuarterGeometry;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ValueError;

class GenplanGeometryTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_quarter_accepts_distinct_two_d_and_three_d_geometries(): void
    {
        $quarter = Quarter::factory()->create();
        $threeD = $quarter->geometryFor(GenplanMode::ThreeD);
        $twoD = $quarter->geometries()->create([
            'mode' => GenplanMode::TwoD,
            'polygon_data' => $this->polygon(0.55),
            'label_x' => '0.7000004',
            'label_y' => 1,
        ]);

        $this->assertSame($quarter->id, $twoD->quarter_id);
        $this->assertSame($quarter->id, $threeD->quarter_id);
        $this->assertCount(2, $quarter->geometries()->get());
        $this->assertNotSame($twoD->polygon_data, $threeD->polygon_data);
        $this->assertSame('0.700000', $twoD->label_x);
        $this->assertSame('550,550 750,550 750,750 550,750', app(NormalizedGeometry::class)->svgPoints($twoD->polygon_data));
    }

    public function test_duplicate_entity_mode_geometry_is_rejected(): void
    {
        $quarter = Quarter::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);
        $quarter->geometries()->create([
            'mode' => GenplanMode::ThreeD,
            'polygon_data' => $this->polygon(0.5),
        ]);
    }

    public function test_invalid_mode_is_rejected_by_typed_geometry(): void
    {
        $this->expectException(ValueError::class);

        QuarterGeometry::make(['mode' => 'map'])->mode;
    }

    public function test_polygon_rejects_short_malformed_and_out_of_range_coordinates(): void
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
                $this->assertStringStartsWith('polygon_data', array_key_first($exception->errors()));
            }
        }
    }

    public function test_geometry_model_rejects_invalid_marker_coordinate(): void
    {
        $point = InfrastructurePoint::factory()->create();

        $this->expectException(ValidationException::class);
        InfrastructurePointGeometry::create([
            'infrastructure_point_id' => $point->id,
            'mode' => GenplanMode::TwoD,
            'marker_x' => 1.001,
            'marker_y' => 0.5,
        ]);
    }

    public function test_mode_queries_never_fallback_to_geometry_from_another_projection(): void
    {
        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id]);
        $quarter->geometries()->where('mode', GenplanMode::ThreeD->value)->delete();
        $quarter->geometries()->create(['mode' => GenplanMode::TwoD, 'polygon_data' => $this->polygon(0.55)]);

        $twoD = app(GenplanPublicQuery::class)->quarter($quarter->slug, GenplanMode::TwoD);
        $threeD = app(GenplanPublicQuery::class)->quarter($quarter->slug, GenplanMode::ThreeD);

        $this->assertSame(GenplanMode::TwoD, $twoD->geometries->sole()->mode);
        $this->assertTrue($threeD->geometries->isEmpty());
        $this->assertNull($threeD->geometryFor(GenplanMode::ThreeD));
    }

    public function test_incompatible_mobile_asset_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        Genplan::factory()->create([
            'mobile_image_3d' => 'genplan/testing/mobile-3d.webp',
            'mobile_image_3d_is_compatible' => false,
        ]);
    }

    public function test_plot_money_and_status_contracts_remain_exact_and_typed(): void
    {
        $plot = Plot::factory()->create([
            'area' => '12.345',
            'price' => '1234567.895',
            'price_per_sotka' => '100000.005',
        ])->refresh();

        $this->assertSame('12.35', $plot->area);
        $this->assertSame('1234567.90', $plot->price);
        $this->assertSame('100000.01', $plot->price_per_sotka);

        $this->expectException(ValueError::class);
        Quarter::factory()->make(['status' => 'invented-status']);
    }

    private function polygon(float $start): array
    {
        return [
            ['x' => $start, 'y' => $start],
            ['x' => $start + 0.2, 'y' => $start],
            ['x' => $start + 0.2, 'y' => $start + 0.2],
            ['x' => $start, 'y' => $start + 0.2],
        ];
    }
}
