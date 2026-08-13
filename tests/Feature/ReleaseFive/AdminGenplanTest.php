<?php

namespace Tests\Feature\ReleaseFive;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\GenplanMode;
use App\Domain\Genplan\InfrastructurePoint;
use App\Domain\Genplan\Plot;
use App\Domain\Genplan\Quarter;
use App\Domain\Genplan\SurroundingPlace;
use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Filament\Resources\InfrastructurePoints\Pages\CreateInfrastructurePoint;
use App\Filament\Resources\Plots\Pages\EditPlot;
use App\Filament\Resources\Quarters\Pages\CreateQuarter;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AdminGenplanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guest_and_sales_manager_cannot_access_genplan_admin(): void
    {
        $this->get('/admin/genplans')->assertRedirect('/admin/login');

        $salesManager = User::factory()->create();
        $salesManager->assignRole(RoleName::SalesManager->value);

        $this->actingAs($salesManager)->get('/admin/genplans')->assertForbidden();
        $this->get('/admin/plots')->assertForbidden();
    }

    public function test_viewer_can_read_all_foundation_resources_but_cannot_manage_them(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole(RoleName::Viewer->value);
        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id]);
        $plot = Plot::factory()->create(['quarter_id' => $quarter->id]);
        InfrastructurePoint::factory()->create(['genplan_id' => $genplan->id]);
        SurroundingPlace::factory()->create();

        $this->actingAs($viewer);
        foreach (['genplans', 'quarters', 'plots', 'infrastructure-points', 'surrounding-places'] as $resource) {
            $this->get("/admin/{$resource}")->assertOk();
            $this->get("/admin/{$resource}/create")->assertForbidden();
        }

        $this->get("/admin/genplans/{$genplan->id}/edit")->assertForbidden();
        $this->get("/admin/quarters/{$quarter->id}/edit")->assertForbidden();
        $this->get("/admin/plots/{$plot->id}/edit")->assertForbidden();
    }

    public function test_explicit_manage_permissions_allow_crud_without_changing_role_matrix(): void
    {
        $manager = User::factory()->create();
        $manager->givePermissionTo([
            PermissionName::AdminAccess->value,
            PermissionName::GenplanView->value,
            PermissionName::GenplanManage->value,
            PermissionName::PlotsView->value,
            PermissionName::PlotsManage->value,
        ]);

        $this->actingAs($manager)->get('/admin/genplans/create')->assertOk();
        $this->get('/admin/quarters/create')->assertOk();
        $this->get('/admin/plots/create')->assertOk();
        $this->get('/admin/infrastructure-points/create')->assertOk();
        $this->get('/admin/surrounding-places/create')->assertOk();

        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id]);
        $plot = Plot::factory()->create(['quarter_id' => $quarter->id]);

        $this->assertTrue(Gate::forUser($manager)->allows('update', $genplan));
        $this->assertTrue(Gate::forUser($manager)->allows('update', $plot));
        $this->assertTrue(Gate::forUser($manager)->allows('delete', $plot));
    }

    public function test_genplan_manager_can_save_distinct_two_d_and_three_d_quarter_geometry(): void
    {
        $manager = $this->genplanManager();
        $genplan = Genplan::factory()->create();

        Livewire::actingAs($manager)
            ->test(CreateQuarter::class)
            ->fillForm([
                'genplan_id' => $genplan->id,
                'name' => 'Два вида',
                'slug' => 'two-views',
                'status' => 'available',
                'geometry_2d_polygon_data' => $this->polygon(0.1),
                'geometry_3d_polygon_data' => $this->polygon(0.6),
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $quarter = Quarter::query()->where('slug', 'two-views')->firstOrFail();
        $this->assertCount(2, $quarter->geometries);
        $this->assertSame(0.1, $quarter->geometryFor(GenplanMode::TwoD)->polygon_data[0]['x']);
        $this->assertSame(0.6, $quarter->geometryFor(GenplanMode::ThreeD)->polygon_data[0]['x']);
    }

    public function test_quarter_form_rejects_invalid_two_d_and_three_d_repeaters(): void
    {
        $manager = $this->genplanManager();
        $genplan = Genplan::factory()->create();

        Livewire::actingAs($manager)
            ->test(CreateQuarter::class)
            ->fillForm([
                'genplan_id' => $genplan->id,
                'name' => 'Ошибочный квартал',
                'slug' => 'invalid-quarter',
                'status' => 'available',
                'geometry_2d_polygon_data' => [
                    ['x' => 0.1, 'y' => 0.1],
                    ['x' => 1.2, 'y' => 0.1],
                ],
                'geometry_3d_polygon_data' => [
                    ['x' => 0.1, 'y' => 0.1],
                    ['x' => 0.2, 'y' => 0.1],
                ],
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'geometry_2d_polygon_data',
                'geometry_2d_polygon_data.1.x',
                'geometry_3d_polygon_data',
            ]);

        $this->assertDatabaseMissing('quarters', ['slug' => 'invalid-quarter']);
    }

    public function test_plot_geometry_update_requires_plots_manage(): void
    {
        $plotManager = User::factory()->create();
        $plotManager->givePermissionTo([
            PermissionName::AdminAccess->value,
            PermissionName::PlotsView->value,
            PermissionName::PlotsManage->value,
        ]);
        $plot = Plot::factory()->create();

        Livewire::actingAs($plotManager)
            ->test(EditPlot::class, ['record' => $plot->getRouteKey()])
            ->fillForm([
                'geometry_2d_polygon_data' => $this->polygon(0.2),
                'geometry_2d_marker_x' => 0.25,
                'geometry_2d_marker_y' => 0.35,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(0.2, $plot->fresh()->geometryFor(GenplanMode::TwoD)->polygon_data[0]['x']);

        $viewer = User::factory()->create();
        $viewer->assignRole(RoleName::Viewer->value);
        $this->actingAs($viewer)->get("/admin/plots/{$plot->id}/edit")->assertForbidden();
    }

    public function test_infrastructure_form_saves_distinct_mode_positions(): void
    {
        $manager = $this->genplanManager();
        $genplan = Genplan::factory()->create();

        Livewire::actingAs($manager)
            ->test(CreateInfrastructurePoint::class)
            ->fillForm([
                'genplan_id' => $genplan->id,
                'name' => 'Две позиции',
                'category' => 'playground',
                'show_on_2d' => true,
                'show_on_3d' => true,
                'geometry_2d_marker_x' => 0.2,
                'geometry_2d_marker_y' => 0.3,
                'geometry_3d_marker_x' => 0.7,
                'geometry_3d_marker_y' => 0.8,
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $point = InfrastructurePoint::query()->where('name', 'Две позиции')->firstOrFail();
        $this->assertCount(2, $point->geometries);
        $this->assertSame('0.200000', $point->geometryFor(GenplanMode::TwoD)->marker_x);
        $this->assertSame('0.700000', $point->geometryFor(GenplanMode::ThreeD)->marker_x);
    }

    public function test_parent_deletion_is_protected_when_dependencies_exist(): void
    {
        $manager = $this->genplanManager();
        $genplan = Genplan::factory()->create();
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id]);
        Plot::factory()->create(['quarter_id' => $quarter->id]);

        $this->assertFalse(Gate::forUser($manager)->allows('delete', $quarter));
        $this->assertFalse(Gate::forUser($manager)->allows('delete', $genplan));
    }

    private function genplanManager(): User
    {
        $manager = User::factory()->create();
        $manager->givePermissionTo([
            PermissionName::AdminAccess->value,
            PermissionName::GenplanView->value,
            PermissionName::GenplanManage->value,
        ]);

        return $manager;
    }

    private function polygon(float $start): array
    {
        return [
            ['x' => $start, 'y' => $start],
            ['x' => $start + 0.1, 'y' => $start],
            ['x' => $start + 0.1, 'y' => $start + 0.1],
        ];
    }
}
