<?php

namespace Tests\Feature\ReleaseThree;

use App\Domain\Blog\BlogPostStatus;
use App\Domain\Settings\SettingsRepository;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\HomePage;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_root_is_the_real_server_rendered_home_and_uses_settings(): void
    {
        app(SettingsRepository::class)->set('contacts.sales_phone', '+7 999 123-45-67', 'contacts', true);
        app(SettingsRepository::class)->set('contacts.village_address', 'Можайский округ, тестовый адрес', 'contacts', true);
        HomePage::query()->create(array_replace(HomePage::defaultContent(), [
            'hero_title' => 'Настоящая главная RelaxLand',
            'seo_title' => 'SEO главной',
        ]));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Настоящая главная RelaxLand')
            ->assertSee('SEO главной')
            ->assertSee('+7 999 123-45-67')
            ->assertSee('Можайский округ, тестовый адрес')
            ->assertSee('role="tablist"', false)
            ->assertSee('data-season-tab', false)
            ->assertSee('Открыть генплан')
            ->assertSee(route('genplan.index'), false)
            ->assertDontSee('/css/filament', false)
            ->assertDontSee('/js/filament', false);
    }

    public function test_only_active_home_content_is_selected(): void
    {
        HomePage::query()->create(array_replace(HomePage::defaultContent(), ['is_active' => false, 'hero_title' => 'Скрытая версия главной']));
        HomePage::query()->create(array_replace(HomePage::defaultContent(), ['is_active' => true, 'hero_title' => 'Опубликованная версия главной']));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Опубликованная версия главной')
            ->assertDontSee('Скрытая версия главной');
    }

    public function test_visit_section_contains_provider_independent_interactive_map_controls(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-home-map', false)
            ->assertSee('class="home-visit__map-image"', false)
            ->assertSee('data-home-map-zoom-in', false)
            ->assertSee('data-home-map-zoom-out', false)
            ->assertSee('data-home-map-reset', false)
            ->assertDontSee('map-widget', false)
            ->assertDontSee('apikey', false);
    }

    public function test_only_active_stories_are_rendered_in_stable_order(): void
    {
        Story::factory()->create(['title' => 'Вторая история', 'sort_order' => 20]);
        Story::factory()->create(['title' => 'Первая история', 'sort_order' => 10]);
        Story::factory()->create(['title' => 'Скрытая история', 'sort_order' => 0, 'is_active' => false]);

        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['Первая история', 'Вторая история'])
            ->assertDontSee('Скрытая история');

        $this->assertSame(['Первая история', 'Вторая история'], $response->viewData('stories')->pluck('title')->all());
    }

    public function test_blog_preview_contains_only_four_public_posts(): void
    {
        $category = BlogCategory::factory()->create();
        $inactiveCategory = BlogCategory::factory()->inactive()->create();
        $visible = collect(range(1, 5))->map(fn (int $index): BlogPost => BlogPost::factory()->for($category, 'category')->create([
            'title' => "Публичная статья {$index}",
            'published_at' => now()->subDays($index),
        ]));
        $draft = BlogPost::factory()->for($category, 'category')->draft()->create(['title' => 'Черновик главной']);
        $future = BlogPost::factory()->for($category, 'category')->future()->create(['title' => 'Будущая статья главной']);
        $inactive = BlogPost::factory()->for($inactiveCategory, 'category')->create(['title' => 'Статья скрытой категории']);

        $response = $this->get(route('home'))
            ->assertOk()
            ->assertDontSee($visible->last()->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($future->title)
            ->assertDontSee($inactive->title);

        $posts = $response->viewData('blogPosts');
        $this->assertCount(4, $posts);
        $this->assertSame($visible->take(4)->pluck('id')->all(), $posts->pluck('id')->all());
        foreach ($posts as $post) {
            $response->assertSee('href="'.route('blog.show', $post->slug).'"', false)->assertSee($post->title);
        }
        $this->assertTrue($posts->every(fn (BlogPost $post): bool => $post->status === BlogPostStatus::Published
            && $post->relationLoaded('category')
            && ! array_key_exists('content', $post->getAttributes())));
    }

    public function test_fixed_home_collections_are_sanitized_without_raw_html(): void
    {
        $home = HomePage::query()->create(array_replace(HomePage::defaultContent(), [
            'benefits' => [[
                'title' => '<script>alert(1)</script>Безопасное преимущество',
                'text' => '<img src=x onerror=alert(2)>Только текст',
                'unknown' => 'не должно сохраниться',
            ]],
        ]));

        $this->assertSame([
            'title' => 'alert(1)Безопасное преимущество',
            'text' => 'Только текст',
        ], $home->benefits[0]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Безопасное преимущество')
            ->assertDontSee('<script>', false)
            ->assertDontSee('onerror', false);
    }

    public function test_care_carousel_renders_editable_plain_text_content_and_preset_media(): void
    {
        $home = HomePage::query()->create(array_replace(HomePage::defaultContent(), [
            'care_items' => [[
                'title' => '<b>Редактируемая безопасность</b>',
                'text' => "Охрана 24/7\nУмный домофон",
                'note' => '<script>alert(1)</script>Примечание редактора',
                'image_preset' => 'security',
                'image_alt' => 'Камера на территории',
                'icon' => 'security',
                'unknown' => 'не должно сохраниться',
            ]],
        ]));

        $this->assertSame([
            'title' => 'Редактируемая безопасность',
            'text' => "Охрана 24/7\nУмный домофон",
            'note' => 'alert(1)Примечание редактора',
            'image_alt' => 'Камера на территории',
            'image_preset' => 'security',
            'icon' => 'security',
        ], $home->care_items[0]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-home-care', false)
            ->assertSee('data-home-care-tab', false)
            ->assertSee('home-care-security.webp', false)
            ->assertSee('<li>Охрана 24/7</li>', false)
            ->assertSee('<li>Умный домофон</li>', false)
            ->assertSee('Примечание редактора')
            ->assertDontSee('<script>', false);
    }

    public function test_rhythm_storyboard_uses_editable_slides_and_fixed_design_layers(): void
    {
        HomePage::query()->create(array_replace(HomePage::defaultContent(), [
            'life_scenarios' => [[
                'label' => 'Тестовое утро',
                'title' => 'Редактируемый сценарий',
                'text' => 'Описание из административной панели',
                'image' => 'home/collections/rhythm-test.jpg',
                'image_alt' => 'Редактируемое изображение',
            ]],
        ]));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-home-rhythm', false)
            ->assertSee('Тестовое утро')
            ->assertSee('Редактируемый сценарий')
            ->assertSee('Описание из административной панели')
            ->assertSee('/storage/home/collections/rhythm-test.jpg', false)
            ->assertSee('home-rhythm-rabbit.svg', false)
            ->assertSee('home-rhythm-hedgehog.svg', false)
            ->assertSee('home-rhythm-moose.svg', false)
            ->assertSee('home-rhythm-dog.svg', false);
    }
}
