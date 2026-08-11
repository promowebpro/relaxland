<?php

namespace Tests\Feature\ReleaseOne;

use App\Domain\Settings\SettingsRepository;
use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Filament\Pages\SiteSettings as SiteSettingsPage;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_user_without_settings_manage_cannot_change_settings(): void
    {
        $viewer = $this->userWithRole(RoleName::Viewer);

        $this->actingAs($viewer);
        $this->assertTrue($viewer->can(PermissionName::SettingsView->value));
        $this->assertTrue(SiteSettingsPage::canAccess());
        $this->assertTrue($viewer->canAccessPanel(Filament::getPanel('admin')));

        $this->get('/admin/site-settings')
            ->assertOk();

        Livewire::actingAs($viewer)
            ->test(SiteSettingsPage::class)
            ->set('data.primary_phone', '+7 000 000-00-00')
            ->call('save')
            ->assertForbidden();

        $this->assertNull(app(SettingsRepository::class)->get('contacts.phone'));
    }

    public function test_user_without_content_permissions_cannot_manage_legal_documents(): void
    {
        $salesManager = $this->userWithRole(RoleName::SalesManager);

        $this->actingAs($salesManager)
            ->get('/admin/legal-documents')
            ->assertForbidden();
    }

    public function test_content_manager_can_manage_legal_documents_but_not_site_settings(): void
    {
        $contentManager = $this->userWithRole(RoleName::ContentManager);

        $this->actingAs($contentManager);
        $this->assertTrue($contentManager->can(PermissionName::ContentView->value));

        $this->get('/admin/legal-documents')
            ->assertOk();

        Livewire::actingAs($contentManager)
            ->test(SiteSettingsPage::class)
            ->set('data.primary_phone', '+7 000 000-00-00')
            ->call('save')
            ->assertForbidden();
    }

    public function test_super_admin_can_update_site_settings(): void
    {
        $superAdmin = $this->userWithRole(RoleName::SuperAdmin);

        $this->actingAs($superAdmin);

        $this->get('/admin/site-settings')->assertOk();
        $this->get('/admin/legal-documents')->assertOk();

        Livewire::actingAs($superAdmin)
            ->test(SiteSettingsPage::class)
            ->set('data.primary_phone', '+7 4012 555-010')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('+7 4012 555-010', app(SettingsRepository::class)->get('contacts.phone'));
    }

    private function userWithRole(RoleName $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }
}
