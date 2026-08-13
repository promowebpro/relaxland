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

    public function test_public_page_has_a_server_rendered_empty_state_without_active_genplan(): void
    {
        Genplan::factory()->create(['is_active' => false]);

        $this->get(route('genplan.index'))
            ->assertOk()
            ->assertSee('Генплан пока не опубликован')
            ->assertSee('Узнать о проекте')
            ->assertDontSee('<svg class="genplan-overlay"', false);

        $this->getJson(route('api.genplan.overview'))->assertNotFound();
    }

    public function test_public_page_renders_active_geometry_in_ssr_and_excludes_hidden_content(): void
    {
        $genplan = Genplan::factory()->create(['name' => 'Генплан Foundation']);
        $visible = Quarter::factory()->create([
            'genplan_id' => $genplan->id,
            'name' => 'Квартал Северный',
            'slug' => 'north',
        ]);
        Quarter::factory()->create([
            'genplan_id' => $genplan->id,
            'name' => 'Скрытый квартал',
            'status' => QuarterStatus::Hidden,
        ]);
        $point = InfrastructurePoint::factory()->create([
            'genplan_id' => $genplan->id,
            'name' => 'Детская площадка',
        ]);
        InfrastructurePoint::factory()->create([
            'genplan_id' => $genplan->id,
            'name' => 'Скрытый объект',
            'is_active' => false,
        ]);
        SurroundingPlace::factory()->create(['name' => 'Магазин рядом']);
        SurroundingPlace::factory()->create(['name' => 'Скрытое место', 'is_active' => false]);

        $response = $this->get(route('genplan.index'))
            ->assertOk()
            ->assertSee('Генплан Foundation')
            ->assertSee('Квартал Северный')
            ->assertSee('points="100,100 400,100 400,400 100,400"', false)
            ->assertSee('role="tablist"', false)
            ->assertSee('data-genplan-mode="2d"', false)
            ->assertSee('Детская площадка')
            ->assertSee('Магазин рядом')
            ->assertDontSee('Скрытый квартал')
            ->assertDontSee('Скрытый объект')
            ->assertDontSee('Скрытое место');

        $response->assertSee('aria-label="'.$visible->name.' — Доступен"', false);
        $response->assertSee('transform="translate('.((float) $point->marker_x * 1000), false);
    }

    public function test_public_api_returns_only_active_controlled_dtos(): void
    {
        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create([
            'genplan_id' => $genplan->id,
            'slug' => 'quarter-a',
        ]);
        $inactiveQuarter = Quarter::factory()->create([
            'genplan_id' => $genplan->id,
            'slug' => 'quarter-hidden',
            'is_active' => false,
        ]);
        $visiblePlot = Plot::factory()->create([
            'quarter_id' => $quarter->id,
            'number' => 'A-10',
            'slug' => 'a-10',
            'attributes' => ['internal_note' => 'never expose'],
        ]);
        Plot::factory()->create([
            'quarter_id' => $quarter->id,
            'number' => 'A-11',
            'slug' => 'a-11',
            'is_visible' => false,
        ]);
        Plot::factory()->create([
            'quarter_id' => $quarter->id,
            'number' => 'A-12',
            'slug' => 'a-12',
            'status' => PlotStatus::Hidden,
        ]);

        $this->getJson(route('api.genplan.overview'))
            ->assertOk()
            ->assertJsonPath('data.id', $genplan->id)
            ->assertJsonCount(1, 'data.quarters')
            ->assertJsonMissingPath('data.settings')
            ->assertJsonMissingPath('data.created_at');

        $this->getJson(route('api.genplan.quarters.show', $quarter->slug))
            ->assertOk()
            ->assertJsonPath('data.slug', 'quarter-a')
            ->assertJsonPath('data.polygon.0.x', 0.1);
        $this->getJson(route('api.genplan.quarters.show', $inactiveQuarter->slug))->assertNotFound();

        $this->getJson(route('api.genplan.quarters.plots', $quarter->slug))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visiblePlot->id)
            ->assertJsonMissingPath('data.0.attributes')
            ->assertJsonMissingPath('data.0.created_at');
    }

    public function test_infrastructure_modes_and_surroundings_have_separate_read_only_endpoints(): void
    {
        $genplan = Genplan::factory()->create();
        InfrastructurePoint::factory()->create([
            'genplan_id' => $genplan->id,
            'name' => 'Только 3D',
            'show_on_3d' => true,
            'show_on_2d' => false,
        ]);
        InfrastructurePoint::factory()->create([
            'genplan_id' => $genplan->id,
            'name' => 'Только 2D',
            'show_on_3d' => false,
            'show_on_2d' => true,
        ]);
        SurroundingPlace::factory()->create(['name' => 'Школа']);

        $this->getJson(route('api.genplan.infrastructure', ['mode' => '3d']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Только 3D');
        $this->getJson(route('api.genplan.infrastructure', ['mode' => '2d']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Только 2D');
        $this->getJson(route('api.genplan.infrastructure', ['mode' => 'map']))
            ->assertUnprocessable();
        $this->getJson(route('api.genplan.surroundings'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Школа');

        $this->postJson('/api/genplan', [])->assertMethodNotAllowed();
    }
}
