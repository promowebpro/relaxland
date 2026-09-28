<?php

namespace Tests\Feature\ReleaseThree;

use App\Domain\Users\Enums\RoleName;
use App\Filament\Pages\AboutContentPage;
use App\Models\AboutPage;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_page_renders_saved_content_and_a_live_map(): void
    {
        AboutPage::create(['content' => array_replace(AboutPage::defaults(), [
            'title' => 'Наша команда',
            'trust_text' => 'Новый текст из админки',
            'map_latitude' => 55.7,
            'map_longitude' => 36.2,
            'main_image' => 'about/custom.webp',
            'cards' => [['value' => '42', 'label' => 'проекта', 'text' => 'Новая карточка']],
        ])]);
        $this->get('/about')->assertOk()->assertSee('Наша команда')->assertSee('Новый текст из админки')
            ->assertSee('data-about-map', false)->assertSee('data-latitude="55.7"', false)
            ->assertSee('/storage/about/custom.webp', false)->assertSee('Новая карточка')
            ->assertSee('about-hedgehog.svg')->assertSee('about-skate-dog.svg')
            ->assertDontSee('about-map.webp')->assertSee('destination=55.7,36.2', false);
    }

    public function test_manager_can_edit_content_and_coordinates(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole(RoleName::ContentManager->value);
        Livewire::actingAs($manager)->test(AboutContentPage::class)
            ->set('data.title', 'Новая страница о нас')
            ->set('data.map_latitude', 56.1)
            ->set('data.map_longitude', 37.2)
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('Новая страница о нас', AboutPage::firstOrFail()->content['title']);
        $this->get('/about')->assertOk()->assertSee('Новая страница о нас')->assertSee('data-latitude="56.1"', false);
    }

    public function test_invalid_coordinates_and_viewer_cannot_save(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole(RoleName::ContentManager->value);
        Livewire::actingAs($manager)->test(AboutContentPage::class)
            ->set('data.map_latitude', 200)->call('save')->assertHasFormErrors(['map_latitude']);
        $this->assertDatabaseCount('about_pages', 0);
        $viewer = User::factory()->create();
        $viewer->assignRole(RoleName::Viewer->value);
        Livewire::actingAs($viewer)->test(AboutContentPage::class)->call('save')->assertForbidden();
        $this->assertDatabaseCount('about_pages', 0);
    }
}
