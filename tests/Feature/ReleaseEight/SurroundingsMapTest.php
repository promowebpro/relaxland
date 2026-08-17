<?php

namespace Tests\Feature\ReleaseEight;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\GeographicPoint;
use App\Domain\Genplan\Quarter;
use App\Domain\Genplan\StraightLineDistance;
use App\Domain\Genplan\SurroundingCategory;
use App\Domain\Genplan\SurroundingPlace;
use App\Domain\Settings\SettingsRepository;
use App\Domain\Settings\SiteSettings;
use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Filament\Resources\SurroundingPlaces\Pages\CreateSurroundingPlace;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class SurroundingsMapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_geographic_coordinate_contract_accepts_boundaries_and_rejects_invalid_values(): void
    {
        $point = new GeographicPoint('-90.0000000', '180.0000000');
        $this->assertSame(['latitude' => -90.0, 'longitude' => 180.0], $point->numeric());

        foreach ([['90.0000001', '0'], ['0', '-180.0000001'], ['INF', '0'], ['NaN', '0'], ['1e2', '0']] as [$latitude, $longitude]) {
            try {
                new GeographicPoint($latitude, $longitude);
                $this->fail("Invalid coordinates {$latitude}, {$longitude} were accepted.");
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
    }

    public function test_surrounding_place_rejects_invalid_coordinates_unsafe_url_and_unsafe_image_path(): void
    {
        $genplan = Genplan::factory()->create();

        foreach ([
            ['latitude' => '91', 'longitude' => '36'],
            ['latitude' => '55', 'longitude' => '-181'],
            ['latitude' => '55', 'longitude' => 'INF'],
            ['latitude' => '55', 'longitude' => '36', 'slug' => 'Unsafe Slug'],
            ['latitude' => '55', 'longitude' => '36', 'external_url' => 'http://example.test/route'],
            ['latitude' => '55', 'longitude' => '36', 'image' => '../secret.jpg'],
        ] as $attributes) {
            try {
                SurroundingPlace::factory()->create(['genplan_id' => $genplan->id, ...$attributes]);
                $this->fail('Invalid public map data was accepted.');
            } catch (ValidationException|\InvalidArgumentException $exception) {
                $this->assertNotSame('', $exception->getMessage());
            }
        }
    }

    public function test_public_api_is_scoped_to_current_genplan_and_exposes_only_controlled_fields(): void
    {
        $genplan = Genplan::factory()->create();
        $other = Genplan::factory()->create();
        $place = SurroundingPlace::factory()->create([
            'genplan_id' => $genplan->id,
            'name' => 'Можайское море',
            'slug' => 'mozhayskoe-more',
            'category' => SurroundingCategory::Entertainment,
            'latitude' => '55.5067000',
            'longitude' => '36.0215000',
            'description' => '<script>alert(1)</script>',
            'external_url' => 'https://yandex.ru/maps/?rtext=test',
            'sort_order' => 2,
        ]);
        SurroundingPlace::factory()->create(['genplan_id' => $genplan->id, 'is_active' => false]);
        SurroundingPlace::factory()->create(['genplan_id' => $other->id, 'name' => 'Другой генплан']);

        $response = $this->getJson(route('api.genplan.surroundings'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $place->slug)
            ->assertJsonPath('data.0.category', 'entertainment')
            ->assertJsonPath('data.0.coordinates.latitude', 55.5067)
            ->assertJsonMissingPath('data.0.id')
            ->assertJsonMissingPath('data.0.genplan_id')
            ->assertJsonMissingPath('data.0.created_at');

        $this->assertSame([
            'slug', 'name', 'category', 'category_label', 'category_symbol', 'coordinates',
            'description', 'image_url', 'external_url',
        ], array_keys($response->json('data.0')));
        $this->postJson(route('api.genplan.surroundings'))->assertMethodNotAllowed();
    }

    public function test_invalid_database_coordinates_and_unsafe_legacy_url_are_fail_closed(): void
    {
        $genplan = Genplan::factory()->create();
        $now = now();
        DB::table('surrounding_places')->insert([
            [
                'genplan_id' => $genplan->id, 'name' => 'Неверная точка', 'slug' => 'invalid-point',
                'category' => 'shopping', 'latitude' => '91', 'longitude' => '36', 'description' => null,
                'image' => null, 'external_url' => null, 'sort_order' => 1, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'genplan_id' => $genplan->id, 'name' => 'Небезопасная ссылка', 'slug' => 'unsafe-link',
                'category' => 'shopping', 'latitude' => '55', 'longitude' => '36', 'description' => null,
                'image' => '../secret.jpg', 'external_url' => 'javascript:alert(1)', 'sort_order' => 2, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);

        $this->getJson(route('api.genplan.surroundings'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'unsafe-link')
            ->assertJsonPath('data.0.external_url', null)
            ->assertJsonPath('data.0.image_url', null)
            ->assertJsonMissing(['invalid-point', 'javascript:alert(1)', '../secret.jpg']);
    }

    public function test_category_filter_is_controlled_order_is_deterministic_and_queries_are_bounded(): void
    {
        $genplan = Genplan::factory()->create();
        SurroundingPlace::factory()->create(['genplan_id' => $genplan->id, 'slug' => 'second', 'sort_order' => 2]);
        SurroundingPlace::factory()->create(['genplan_id' => $genplan->id, 'slug' => 'first', 'sort_order' => 1]);
        SurroundingPlace::factory()->create(['genplan_id' => $genplan->id, 'slug' => 'sport', 'category' => SurroundingCategory::Sport, 'sort_order' => 1]);
        $queries = 0;
        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            $queries++;
        });

        $response = $this->getJson(route('api.genplan.surroundings', ['category' => 'shopping']))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'first')
            ->assertJsonPath('data.1.slug', 'second');

        $this->assertLessThanOrEqual(3, $queries);
        $this->assertLessThan(50_000, strlen($response->getContent()));
        $this->getJson(route('api.genplan.surroundings', ['category' => 'invented']))->assertUnprocessable();
    }

    public function test_public_slug_is_unique_per_genplan_scope(): void
    {
        $first = Genplan::factory()->create();
        $second = Genplan::factory()->create();
        SurroundingPlace::factory()->create(['genplan_id' => $first->id, 'slug' => 'museum']);
        SurroundingPlace::factory()->create(['genplan_id' => $second->id, 'slug' => 'museum']);

        $this->expectException(QueryException::class);
        SurroundingPlace::factory()->create(['genplan_id' => $first->id, 'slug' => 'museum']);
    }

    public function test_surroundings_url_state_is_server_rendered_canonical_and_mutually_exclusive(): void
    {
        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id, 'slug' => 'north']);
        $place = SurroundingPlace::factory()->create([
            'genplan_id' => $genplan->id,
            'slug' => 'museum',
            'name' => 'Музей-заповедник',
            'description' => '<img src=x onerror=alert(1)>',
        ]);

        $this->get(route('genplan.index', [
            'view' => 'surroundings', 'place' => $place->slug, 'quarter' => $quarter->slug,
            'plot' => 'secret', 'point' => 'secret',
        ]))
            ->assertOk()
            ->assertSee('data-initial-tab="surroundings"', false)
            ->assertSee('data-initial-place="museum"', false)
            ->assertSee('data-initial-quarter=""', false)
            ->assertSee('data-initial-point=""', false)
            ->assertSee('data-surrounding-card="museum"', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertDontSee('place='.$place->id, false);

        $this->get(route('genplan.index', ['place' => $place->slug]))
            ->assertOk()
            ->assertSee('data-initial-tab="genplan"', false)
            ->assertSee('data-initial-place=""', false);

        $place->update(['is_active' => false]);
        $this->get(route('genplan.index', ['view' => 'surroundings', 'place' => $place->slug]))
            ->assertOk()
            ->assertSee('data-initial-place=""', false)
            ->assertDontSee('data-surrounding-card="museum"', false);
    }

    public function test_ssr_fallback_distance_and_external_route_remain_available_without_provider_key(): void
    {
        config()->set('surroundings.api_key', null);
        $genplan = Genplan::factory()->create();
        $place = SurroundingPlace::factory()->create([
            'genplan_id' => $genplan->id,
            'name' => 'Конный клуб',
            'slug' => 'horse-club',
            'latitude' => '55.1000000',
            'longitude' => '36.1000000',
            'external_url' => 'https://yandex.ru/maps/?rtext=55,36',
        ]);
        $settings = app(SettingsRepository::class);
        $settings->set('contacts.village_latitude', '55.0000000', 'contacts', true);
        $settings->set('contacts.village_longitude', '36.0000000', 'contacts', true);

        $this->get(route('genplan.index', ['view' => 'surroundings', 'place' => $place->slug]))
            ->assertOk()
            ->assertSee('Конный клуб')
            ->assertSee('по прямой')
            ->assertSee('target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('"apiKey":null', false)
            ->assertSee('Карта дополняет список')
            ->assertDontSee('<script src="https://api-maps.yandex.ru', false);
    }

    public function test_map_sdk_is_not_loaded_on_home_or_default_genplan_and_direct_view_has_lazy_contract(): void
    {
        Genplan::factory()->create();
        config()->set('surroundings.api_key', 'public-browser-test-key');

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('relaxland-yandex-maps-v3', false)
            ->assertDontSee('data-surroundings-config', false);

        $this->get(route('genplan.index'))
            ->assertOk()
            ->assertSee('data-surroundings-config', false)
            ->assertDontSee('<script src="https://api-maps.yandex.ru', false);

        $this->get(route('genplan.index', ['view' => 'surroundings']))
            ->assertOk()
            ->assertSee('data-initial-tab="surroundings"', false)
            ->assertSee('"provider":"yandex-js-v3"', false)
            ->assertSee('"apiKey":"public-browser-test-key"', false);
    }

    public function test_settlement_settings_are_typed_and_distance_uses_haversine_formula(): void
    {
        $settings = app(SettingsRepository::class);
        $settings->set('contacts.village_latitude', '55.0000000', 'contacts', true);
        $settings->set('contacts.village_longitude', '36.0000000', 'contacts', true);
        $point = app(SiteSettings::class)->settlementPoint();

        $this->assertNotNull($point);
        $distance = app(StraightLineDistance::class)->kilometres(
            new GeographicPoint('0', '0'),
            new GeographicPoint('0', '1'),
        );
        $this->assertEqualsWithDelta(111.2, $distance, 0.1);

        $settings->set('contacts.village_latitude', 'NaN', 'contacts', true);
        $this->assertNull(app(SiteSettings::class)->settlementPoint());
    }

    public function test_surrounding_place_admin_keeps_genplan_permissions_and_rejects_invalid_form_data(): void
    {
        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $viewer = User::factory()->create();
        $viewer->assignRole(RoleName::Viewer->value);
        $this->actingAs($viewer)->get('/admin/surrounding-places')->assertOk();
        $this->get('/admin/surrounding-places/create')->assertForbidden();

        $manager = User::factory()->create();
        $manager->givePermissionTo([
            PermissionName::AdminAccess->value,
            PermissionName::GenplanView->value,
            PermissionName::GenplanManage->value,
        ]);
        $genplan = Genplan::factory()->create();

        Livewire::actingAs($manager)
            ->test(CreateSurroundingPlace::class)
            ->fillForm([
                'genplan_id' => $genplan->id,
                'name' => 'Ошибочное место',
                'slug' => 'invalid-place',
                'category' => 'shopping',
                'latitude' => 91,
                'longitude' => 36,
                'external_url' => 'http://example.test/route',
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['latitude', 'external_url']);

        $this->assertDatabaseMissing('surrounding_places', ['slug' => 'invalid-place']);
    }
}
