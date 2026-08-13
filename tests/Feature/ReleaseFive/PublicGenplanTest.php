<?php

namespace Tests\Feature\ReleaseFive;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\InfrastructurePoint;
use App\Domain\Genplan\Plot;
use App\Domain\Genplan\PlotStatus;
use App\Domain\Genplan\Quarter;
use App\Domain\Genplan\QuarterStatus;
use App\Domain\Genplan\SurroundingPlace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicGenplanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_public_page_has_server_rendered_empty_state_without_active_genplan(): void
    {
        Genplan::factory()->create(['is_active' => false]);

        $this->get(route('genplan.index'))
            ->assertOk()
            ->assertSee('Генплан пока не опубликован')
            ->assertDontSee('<svg class="genplan-overlay"', false);
        $this->getJson(route('api.genplan.overview'))->assertNotFound();
    }

    public function test_ssr_contains_distinct_mode_layers_and_defaults_to_three_d(): void
    {
        $genplan = Genplan::factory()->create(['name' => 'Multi-view Genplan']);
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id, 'name' => 'Квартал Северный']);
        $quarter->geometries()->updateOrCreate(['mode' => '3d'], ['polygon_data' => $this->polygon(0.1)]);
        $quarter->geometries()->create(['mode' => '2d', 'polygon_data' => $this->polygon(0.6)]);
        $point = InfrastructurePoint::factory()->create(['genplan_id' => $genplan->id, 'name' => 'Площадка']);
        $point->geometries()->updateOrCreate(['mode' => '3d'], ['marker_x' => 0.2, 'marker_y' => 0.3]);
        $point->geometries()->create(['mode' => '2d', 'marker_x' => 0.8, 'marker_y' => 0.7]);

        $this->get(route('genplan.index'))
            ->assertOk()
            ->assertSee('data-geometry-mode="3d"', false)
            ->assertSee('data-geometry-mode="2d"', false)
            ->assertSee('points="100,100 300,100 300,300 100,300"', false)
            ->assertSee('points="600,600 800,600 800,800 600,800"', false)
            ->assertSee('style="--marker-x: 20%; --marker-y: 30%"', false)
            ->assertSee('style="--marker-x: 80%; --marker-y: 70%"', false)
            ->assertSee('data-genplan-mode="3d"', false)
            ->assertSee('aria-pressed="true" data-genplan-mode="3d"', false);
    }

    public function test_missing_mode_geometry_is_not_replaced_by_other_mode(): void
    {
        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id]);
        $quarter->geometries()->where('mode', '2d')->delete();

        $response = $this->get(route('genplan.index'))->assertOk();
        $response->assertSee('data-genplan-geometry-empty="2d" data-has-geometry="false"', false);

        $this->getJson(route('api.genplan.overview', ['mode' => '2d']))
            ->assertOk()
            ->assertJsonPath('data.selected_mode', '2d')
            ->assertJsonPath('data.quarters.0.geometry', null);
        $this->getJson(route('api.genplan.overview', ['mode' => '3d']))
            ->assertJsonPath('data.quarters.0.geometry.mode', '3d');
    }

    public function test_mode_aware_api_returns_only_requested_geometry_and_controlled_fields(): void
    {
        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id, 'slug' => 'quarter-a']);
        $quarter->geometries()->updateOrCreate(['mode' => '3d'], ['polygon_data' => $this->polygon(0.1)]);
        $quarter->geometries()->create(['mode' => '2d', 'polygon_data' => $this->polygon(0.6)]);
        $visiblePlot = Plot::factory()->create([
            'quarter_id' => $quarter->id,
            'number' => 'A-10',
            'slug' => 'a-10',
            'attributes' => ['internal_note' => 'never expose'],
        ]);
        $visiblePlot->geometries()->create(['mode' => '2d', 'marker_x' => 0.8, 'marker_y' => 0.8]);
        Plot::factory()->create(['quarter_id' => $quarter->id, 'is_visible' => false]);
        Plot::factory()->create(['quarter_id' => $quarter->id, 'status' => PlotStatus::Hidden]);
        $inactive = Quarter::factory()->create(['genplan_id' => $genplan->id, 'is_active' => false]);

        $this->getJson(route('api.genplan.overview', ['mode' => '2d']))
            ->assertOk()
            ->assertJsonPath('data.available_modes', ['2d', '3d'])
            ->assertJsonPath('data.quarters.0.geometry.mode', '2d')
            ->assertJsonPath('data.quarters.0.geometry.polygon.0.x', 0.6)
            ->assertJsonMissingPath('data.settings')
            ->assertJsonMissingPath('data.created_at');

        $this->getJson(route('api.genplan.quarters.show', [$quarter->slug, 'mode' => '3d']))
            ->assertJsonPath('data.geometry.polygon.0.x', 0.1);
        $this->getJson(route('api.genplan.quarters.show', [$inactive->slug, 'mode' => '3d']))->assertNotFound();

        $this->getJson(route('api.genplan.quarters.plots', [$quarter->slug, 'mode' => '2d']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visiblePlot->id)
            ->assertJsonPath('data.0.geometry.mode', '2d')
            ->assertJsonMissingPath('data.0.attributes');

        $this->getJson(route('api.genplan.overview', ['mode' => 'map']))->assertUnprocessable();
    }

    public function test_infrastructure_requires_visibility_and_geometry_for_selected_mode(): void
    {
        $genplan = Genplan::factory()->create();
        $point = InfrastructurePoint::factory()->create([
            'genplan_id' => $genplan->id,
            'name' => 'Разные позиции',
        ]);
        $point->geometries()->updateOrCreate(['mode' => '3d'], ['marker_x' => 0.2, 'marker_y' => 0.3]);
        $point->geometries()->create(['mode' => '2d', 'marker_x' => 0.8, 'marker_y' => 0.7]);
        $missingTwoD = InfrastructurePoint::factory()->create(['genplan_id' => $genplan->id, 'name' => 'Без 2D']);
        $missingTwoD->geometries()->where('mode', '2d')->delete();
        InfrastructurePoint::factory()->create(['genplan_id' => $genplan->id, 'is_active' => false]);
        SurroundingPlace::factory()->create(['name' => 'Школа']);
        SurroundingPlace::factory()->create(['is_active' => false]);

        $this->getJson(route('api.genplan.infrastructure', ['mode' => '2d']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.geometry.marker.x', 0.8);
        $this->getJson(route('api.genplan.infrastructure', ['mode' => '3d']))
            ->assertJsonPath('data.0.geometry.marker.x', 0.2);
        $this->getJson(route('api.genplan.surroundings'))->assertJsonCount(1, 'data');
        $this->postJson('/api/genplan')->assertMethodNotAllowed();
    }

    public function test_hidden_entities_and_home_link_contract_remain_intact(): void
    {
        $genplan = Genplan::factory()->create();
        Quarter::factory()->create(['genplan_id' => $genplan->id, 'status' => QuarterStatus::Hidden, 'name' => 'Скрытый квартал']);

        $this->get(route('genplan.index'))->assertDontSee('Скрытый квартал');
        $this->get(route('home'))->assertSee(route('genplan.index'), false);
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
