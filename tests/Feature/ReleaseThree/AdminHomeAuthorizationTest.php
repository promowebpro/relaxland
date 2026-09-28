<?php

namespace Tests\Feature\ReleaseThree;

use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Filament\Pages\HomeContentPage;
use App\Filament\Resources\Stories\Pages\CreateStory;
use App\Filament\Resources\Stories\Pages\EditStory;
use App\Models\HomePage;
use App\Models\Story;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminHomeAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_content_manager_can_manage_home_and_stories(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole(RoleName::ContentManager->value);

        $this->actingAs($manager);
        $this->get('/admin/home')->assertOk();
        $this->get('/admin/stories')->assertOk();
        $this->get('/admin/stories/create')->assertOk();

        $component = Livewire::actingAs($manager)
            ->test(HomeContentPage::class);
        $firstCareItem = array_key_first($component->get('data.care_items'));

        $component
            ->set('data.hero_title', 'Главная из Filament')
            ->set("data.care_items.{$firstCareItem}.title", 'Слайд из Filament')
            ->set("data.care_items.{$firstCareItem}.text", "Первая строка\nВторая строка")
            ->set("data.care_items.{$firstCareItem}.icon", 'heart')
            ->set('data.is_active', true)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('home_pages', ['hero_title' => 'Главная из Filament', 'is_active' => true]);
        $this->assertSame('Слайд из Filament', HomePage::query()->firstOrFail()->care_items[0]['title']);
    }

    public function test_user_without_publish_permission_cannot_activate_home_or_story(): void
    {
        $editor = $this->editorWithoutPublish();
        $home = HomePage::query()->firstOrFail();
        $home->update(['is_active' => false]);

        Livewire::actingAs($editor)
            ->test(HomeContentPage::class)
            ->set('data.hero_title', 'Изменение редактора')
            ->set('data.is_active', true)
            ->call('save')
            ->assertHasNoFormErrors();

        $home->refresh();
        $this->assertSame('Изменение редактора', $home->hero_title);
        $this->assertFalse($home->is_active);

        Livewire::actingAs($editor)
            ->test(CreateStory::class)
            ->fillForm([
                'title' => 'История редактора',
                'slug' => 'editor-story',
                'sort_order' => 10,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('stories', ['slug' => 'editor-story', 'is_active' => false]);
    }

    public function test_user_without_publish_permission_cannot_change_story_visibility(): void
    {
        $editor = $this->editorWithoutPublish();
        $story = Story::factory()->create(['is_active' => true]);

        Livewire::actingAs($editor)
            ->test(EditStory::class, ['record' => $story->getRouteKey()])
            ->set('data.title', 'Обновлённая история')
            ->set('data.is_active', false)
            ->call('save')
            ->assertHasNoFormErrors();

        $story->refresh();
        $this->assertSame('Обновлённая история', $story->title);
        $this->assertTrue($story->is_active);
    }

    public function test_viewer_has_read_only_home_and_cannot_manage_stories(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole(RoleName::Viewer->value);
        $story = Story::factory()->create();

        $this->actingAs($viewer)->get('/admin/home')->assertOk();
        $this->get('/admin/stories')->assertOk();
        $this->get('/admin/stories/create')->assertForbidden();
        $this->get("/admin/stories/{$story->id}/edit")->assertForbidden();
    }

    private function editorWithoutPublish(): User
    {
        $editor = User::factory()->create();
        $editor->givePermissionTo([
            PermissionName::AdminAccess->value,
            PermissionName::ContentView->value,
            PermissionName::ContentCreate->value,
            PermissionName::ContentUpdate->value,
        ]);

        return $editor;
    }
}
