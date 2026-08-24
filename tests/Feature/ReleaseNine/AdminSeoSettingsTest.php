<?php

namespace Tests\Feature\ReleaseNine;

use App\Domain\Settings\SettingsRepository;
use App\Domain\Users\Enums\RoleName;
use App\Filament\Pages\SiteSettings;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSeoSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_super_admin_can_save_controlled_global_seo_fallbacks(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleName::SuperAdmin->value);

        Livewire::actingAs($admin)
            ->test(SiteSettings::class)
            ->set('data.seo_site_title', 'RelaxLand')
            ->set('data.seo_title_suffix', 'Можайский')
            ->set('data.seo_default_description', 'Жизнь рядом с природой')
            ->set('data.seo_organization_name', 'RelaxLand')
            ->set('data.seo_default_locale', 'ru_RU')
            ->call('save')
            ->assertHasNoErrors();

        $settings = app(SettingsRepository::class);

        $this->assertSame('RelaxLand', $settings->get('seo.site_title'));
        $this->assertSame('Можайский', $settings->get('seo.title_suffix'));
        $this->assertSame('Жизнь рядом с природой', $settings->get('seo.default_description'));
        $this->assertSame('RelaxLand', $settings->get('seo.organization_name'));
        $this->assertSame('ru_RU', $settings->get('seo.default_locale'));
    }

    public function test_content_manager_cannot_save_global_seo_settings(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole(RoleName::ContentManager->value);

        Livewire::actingAs($manager)
            ->test(SiteSettings::class)
            ->set('data.seo_site_title', 'Unauthorized')
            ->call('save')
            ->assertForbidden();

        $this->assertNull(app(SettingsRepository::class)->get('seo.site_title'));
    }
}
