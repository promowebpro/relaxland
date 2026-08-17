<?php

namespace Tests\Feature\ReleaseSix;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\InfrastructurePoint;
use App\Domain\Genplan\Quarter;
use App\Domain\Genplan\QuarterStatus;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GenplanInteractionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_default_state_is_three_d_without_an_implicit_selection(): void
    {
        [$genplan, $quarter, $point] = $this->publishedFixture();

        $this->get(route('genplan.index'))
            ->assertOk()
            ->assertSee('data-initial-tab="genplan"', false)
            ->assertSee('data-initial-mode="3d"', false)
            ->assertSee('data-initial-quarter=""', false)
            ->assertSee('data-initial-point=""', false)
            ->assertSee('data-selection-empty', false)
            ->assertSee('data-quarter-card="quarter-a"  hidden', false)
            ->assertSee('data-point-card="playground"  hidden', false)
            ->assertDontSee('data-plot-item', false);
    }

    public function test_direct_quarter_link_is_server_rendered_and_uses_public_slug(): void
    {
        [$genplan, $quarter] = $this->publishedFixture();

        $this->get(route('genplan.index', ['mode' => '3d', 'quarter' => $quarter->slug]))
            ->assertOk()
            ->assertSee('data-initial-quarter="quarter-a"', false)
            ->assertSee('aria-pressed="true"', false)
            ->assertSee('data-quarter-card="quarter-a"', false)
            ->assertSee('Выбрать участок')
            ->assertSee('data-plots-panel', false);
    }

    public function test_direct_point_link_is_server_rendered_and_mutually_exclusive(): void
    {
        [$genplan, $quarter, $point] = $this->publishedFixture();

        $this->get(route('genplan.index', [
            'mode' => '3d',
            'quarter' => 'unknown',
            'point' => $point->slug,
        ]))
            ->assertOk()
            ->assertSee('data-initial-quarter=""', false)
            ->assertSee('data-initial-point="playground"', false)
            ->assertSee('data-point-card="playground"', false)
            ->assertSee('point=playground', false)
            ->assertDontSee('point='.$point->id, false);

        $this->get(route('genplan.index', [
            'quarter' => $quarter->slug,
            'point' => $point->slug,
        ]))
            ->assertSee('data-initial-quarter="quarter-a"', false)
            ->assertSee('data-initial-point=""', false);
    }

    public function test_mode_change_keeps_only_selection_with_geometry_in_that_mode(): void
    {
        [$genplan, $quarter, $point] = $this->publishedFixture();

        $this->get(route('genplan.index', ['mode' => '2d', 'quarter' => $quarter->slug]))
            ->assertSee('data-initial-mode="2d"', false)
            ->assertSee('data-initial-quarter="quarter-a"', false)
            ->assertSee('plan-2d.webp', false);

        $quarter->geometries()->where('mode', '2d')->delete();

        $this->get(route('genplan.index', ['mode' => '2d', 'quarter' => $quarter->slug]))
            ->assertSee('data-initial-quarter=""', false);

        $this->get(route('genplan.index', ['mode' => '2d', 'point' => $point->slug]))
            ->assertSee('data-initial-point="playground"', false);
    }

    public function test_invalid_query_values_and_hidden_entities_fail_closed(): void
    {
        [$genplan] = $this->publishedFixture();
        $hiddenQuarter = Quarter::factory()->create([
            'genplan_id' => $genplan->id,
            'slug' => 'hidden-quarter',
            'status' => QuarterStatus::Hidden,
        ]);
        $hiddenPoint = InfrastructurePoint::factory()->create([
            'genplan_id' => $genplan->id,
            'slug' => 'hidden-point',
            'is_active' => false,
        ]);

        $this->get('/genplan?mode[]=2d&quarter='.$hiddenQuarter->slug.'&point='.$hiddenPoint->slug)
            ->assertOk()
            ->assertSee('data-initial-mode="3d"', false)
            ->assertSee('data-initial-quarter=""', false)
            ->assertSee('data-initial-point=""', false)
            ->assertDontSee('hidden-quarter', false)
            ->assertDontSee('hidden-point', false);
    }

    public function test_surroundings_ssr_clears_selection_without_loading_a_map_provider(): void
    {
        [$genplan, $quarter, $point] = $this->publishedFixture();

        $this->get(route('genplan.index', [
            'view' => 'surroundings',
            'quarter' => $quarter->slug,
            'point' => $point->slug,
        ]))
            ->assertOk()
            ->assertSee('data-initial-tab="surroundings"', false)
            ->assertSee('data-initial-quarter=""', false)
            ->assertSee('data-initial-point=""', false)
            ->assertSee('Карта дополняет список')
            ->assertSee('data-surroundings-map', false)
            ->assertDontSee('iframe', false);
    }

    public function test_public_render_does_not_query_plots_or_create_an_n_plus_one_loop(): void
    {
        [$genplan] = $this->publishedFixture();
        Quarter::factory()->count(8)->create(['genplan_id' => $genplan->id]);
        InfrastructurePoint::factory()->count(8)->create(['genplan_id' => $genplan->id]);
        $queries = [];
        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            $queries[] = strtolower($query->sql);
        });

        $this->get(route('genplan.index'))->assertOk();

        $this->assertLessThanOrEqual(11, count($queries));
        $this->assertFalse(collect($queries)->contains(fn (string $sql): bool => str_contains($sql, 'plots')));
    }

    public function test_descriptions_are_escaped_and_api_exposes_stable_point_slug(): void
    {
        [$genplan, $quarter, $point] = $this->publishedFixture();
        $quarter->update(['description' => '<script>alert("quarter")</script>']);
        $point->update(['description' => '<img src=x onerror=alert("point")>']);

        $this->get(route('genplan.index', ['point' => $point->slug]))
            ->assertSee('&lt;script&gt;alert(&quot;quarter&quot;)&lt;/script&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(&quot;point&quot;)&gt;', false)
            ->assertDontSee('<script>alert("quarter")</script>', false);

        $this->getJson(route('api.genplan.infrastructure', ['mode' => '3d']))
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'playground')
            ->assertJsonMissingPath('data.0.genplan_id');
    }

    /** @return array{Genplan, Quarter, InfrastructurePoint} */
    private function publishedFixture(): array
    {
        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create([
            'genplan_id' => $genplan->id,
            'slug' => 'quarter-a',
            'name' => 'Квартал A',
        ]);
        $quarter->geometries()->create([
            'mode' => '2d',
            'polygon_data' => [
                ['x' => 0.55, 'y' => 0.55],
                ['x' => 0.8, 'y' => 0.55],
                ['x' => 0.8, 'y' => 0.8],
                ['x' => 0.55, 'y' => 0.8],
            ],
            'label_x' => 0.67,
            'label_y' => 0.67,
        ]);
        $point = InfrastructurePoint::factory()->create([
            'genplan_id' => $genplan->id,
            'slug' => 'playground',
            'name' => 'Детская площадка',
        ]);
        $point->geometries()->create([
            'mode' => '2d',
            'marker_x' => 0.75,
            'marker_y' => 0.7,
        ]);

        return [$genplan, $quarter, $point];
    }
}
