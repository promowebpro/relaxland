<?php

namespace Tests\Feature\ReleaseSeven;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\Plot;
use App\Domain\Genplan\PlotPresentation;
use App\Domain\Genplan\PlotStatus;
use App\Domain\Genplan\Quarter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicPlotSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_default_page_does_not_query_or_render_plots(): void
    {
        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id, 'slug' => 'north']);
        Plot::factory()->create(['quarter_id' => $quarter->id, 'slug' => 'n-1']);
        $queries = [];
        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->get(route('genplan.index'))
            ->assertOk()
            ->assertSee('data-initial-plots-open="false"', false)
            ->assertSee('<script type="application/json" data-initial-plots>[]</script>', false)
            ->assertDontSee('data-plot-slug="n-1"', false);

        $this->assertFalse(collect($queries)->contains(fn (string $sql): bool => str_contains(strtolower($sql), 'from "plots"')));
    }

    public function test_quarter_and_direct_plot_links_are_progressively_rendered_by_slug(): void
    {
        [$quarter, $available] = $this->fixture();
        $otherQuarter = Quarter::factory()->create(['genplan_id' => $quarter->genplan_id, 'slug' => 'south']);
        $foreign = Plot::factory()->create(['quarter_id' => $otherQuarter->id, 'slug' => 's-1']);

        $this->get(route('genplan.index', ['mode' => '3d', 'quarter' => $quarter->slug]))
            ->assertOk()
            ->assertSee('data-initial-plots-open="true"', false)
            ->assertSee('data-plot-slug="n-1"', false)
            ->assertSee('Участок №1')
            ->assertDontSee('internal-secret');

        $this->get(route('genplan.index', ['mode' => '3d', 'quarter' => $quarter->slug, 'plot' => $available->slug]))
            ->assertOk()
            ->assertSee('data-initial-plot="n-1"', false)
            ->assertSee('Узнать об участке')
            ->assertSee('data-lead-quarter="north"', false)
            ->assertSee('data-lead-plot="n-1"', false);

        $this->get(route('genplan.index', ['quarter' => $quarter->slug, 'plot' => $foreign->slug]))
            ->assertOk()
            ->assertSee('data-initial-plot=""', false)
            ->assertDontSee('data-lead-plot="s-1"', false);
    }

    public function test_api_hides_non_public_rows_uses_mode_specific_geometry_and_no_internal_identifiers(): void
    {
        [$quarter, $available] = $this->fixture();
        $available->geometries()->create(['mode' => '2d', 'marker_x' => 0.81, 'marker_y' => 0.72]);
        Plot::factory()->create(['quarter_id' => $quarter->id, 'slug' => 'hidden-status', 'status' => PlotStatus::Hidden]);
        Plot::factory()->create(['quarter_id' => $quarter->id, 'slug' => 'invisible', 'is_visible' => false]);

        $this->getJson(route('api.genplan.quarters.plots', [$quarter->slug, 'mode' => '2d']))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.slug', 'n-1')
            ->assertJsonPath('data.0.geometry.mode', '2d')
            ->assertJsonPath('data.0.geometry.marker.x', 0.81)
            ->assertJsonMissingPath('data.0.id')
            ->assertJsonMissingPath('data.0.attributes')
            ->assertJsonMissing(['hidden-status', 'invisible', 'internal-secret']);

        $this->getJson(route('api.genplan.quarters.plots', [$quarter->slug, 'mode' => '3d']))
            ->assertJsonPath('data.0.geometry.marker.x', 0.2);
    }

    public function test_api_filters_sorts_and_validates_controlled_query(): void
    {
        [$quarter] = $this->fixture();

        $this->getJson(route('api.genplan.quarters.plots', [
            $quarter->slug,
            'mode' => '3d',
            'status' => 'reserved',
            'area_min' => '10.00',
            'area_max' => '20.00',
            'price_min' => '1000000.00',
            'price_max' => '2000000.00',
            'sort' => 'price_asc',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'n-2')
            ->assertJsonPath('data.0.can_inquire', false);

        $this->getJson(route('api.genplan.quarters.plots', [$quarter->slug, 'status' => 'hidden']))->assertUnprocessable();
        $this->getJson(route('api.genplan.quarters.plots', [$quarter->slug, 'area_min' => 20, 'area_max' => 10]))->assertUnprocessable();
        $this->getJson(route('api.genplan.quarters.plots', [$quarter->slug, 'sort' => 'random']))->assertUnprocessable();
    }

    public function test_missing_geometry_remains_in_list_without_cross_mode_fallback(): void
    {
        [$quarter] = $this->fixture();
        $quarter->geometries()->create([
            'mode' => '2d',
            'polygon_data' => [['x' => 0.55, 'y' => 0.55], ['x' => 0.85, 'y' => 0.55], ['x' => 0.85, 'y' => 0.85]],
        ]);
        $plot = Plot::factory()->create(['quarter_id' => $quarter->id, 'number' => '4', 'slug' => 'n-4']);
        $plot->geometries()->delete();

        $this->get(route('genplan.index', ['mode' => '2d', 'quarter' => $quarter->slug, 'plot' => $plot->slug]))
            ->assertOk()
            ->assertSee('data-initial-plot="n-4"', false)
            ->assertSee('На этом виде нет отметки')
            ->assertDontSee('aria-label="Участок №4', false);

        $this->getJson(route('api.genplan.quarters.plots', [$quarter->slug, 'mode' => '2d']))
            ->assertJsonFragment(['slug' => 'n-4', 'geometry' => null]);
    }

    public function test_exact_decimal_labels_do_not_use_float_money_math(): void
    {
        $this->assertSame('12,35 сот.', PlotPresentation::area('12.35'));
        $this->assertSame('1 234 567,89 ₽', PlotPresentation::money('1234567.89'));
        $this->assertSame('99 999,99 ₽/сот.', PlotPresentation::moneyPerSotka('99999.99'));
        $this->assertSame('Не указана', PlotPresentation::moneyPerSotka(null));
        $this->assertSame('Цена по запросу', PlotPresentation::money(null));
    }

    public function test_plot_description_is_escaped_and_filters_cannot_select_an_excluded_plot(): void
    {
        [$quarter, $plot] = $this->fixture();
        $plot->update(['description' => '<img src=x onerror=alert("plot")>']);

        $this->get(route('genplan.index', ['quarter' => $quarter->slug, 'plot' => $plot->slug]))
            ->assertOk()
            ->assertDontSee('<img src=x onerror=alert("plot")>', false)
            ->assertSee('&lt;img src=x onerror=alert(&quot;plot&quot;)&gt;', false);

        $this->get(route('genplan.index', [
            'quarter' => $quarter->slug,
            'plot' => $plot->slug,
            'status' => 'reserved',
        ]))
            ->assertOk()
            ->assertSee('data-initial-plot=""', false);
    }

    public function test_quarter_plot_api_has_a_bounded_query_count_and_payload(): void
    {
        [$quarter] = $this->fixture();
        Plot::factory()->count(30)->create(['quarter_id' => $quarter->id]);
        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        $response = $this->getJson(route('api.genplan.quarters.plots', [$quarter->slug, 'mode' => '3d']))
            ->assertOk()
            ->assertJsonCount(33, 'data');

        $this->assertLessThanOrEqual(5, $queries);
        $this->assertLessThan(150_000, strlen($response->getContent()));
    }

    /** @return array{Quarter, Plot} */
    private function fixture(): array
    {
        $genplan = Genplan::factory()->create(['name' => 'RelaxLand Test']);
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id, 'name' => 'Северный', 'slug' => 'north']);
        $available = Plot::factory()->create([
            'quarter_id' => $quarter->id,
            'number' => '1',
            'slug' => 'n-1',
            'area' => '8.25',
            'price' => '900000.00',
            'price_per_sotka' => '109090.91',
            'attributes' => ['note' => 'internal-secret'],
        ]);
        Plot::factory()->create([
            'quarter_id' => $quarter->id,
            'number' => '2',
            'slug' => 'n-2',
            'area' => '12.50',
            'price' => '1500000.00',
            'status' => PlotStatus::Reserved,
        ]);
        Plot::factory()->create([
            'quarter_id' => $quarter->id,
            'number' => '3',
            'slug' => 'n-3',
            'area' => '21.00',
            'price' => null,
            'status' => PlotStatus::Sold,
        ]);

        return [$quarter, $available];
    }
}
