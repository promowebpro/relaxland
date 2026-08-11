<?php

namespace Tests\Feature\ReleaseTwo;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBlogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_listing_only_shows_published_posts_from_active_categories(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Жизнь посёлка']);
        $inactiveCategory = BlogCategory::factory()->inactive()->create();
        $published = BlogPost::factory()->for($category, 'category')->create(['title' => 'Видимая статья']);
        $draft = BlogPost::factory()->for($category, 'category')->draft()->create(['title' => 'Черновик статьи']);
        $future = BlogPost::factory()->for($category, 'category')->future()->create(['title' => 'Будущая статья']);
        $inactive = BlogPost::factory()->for($inactiveCategory, 'category')->create(['title' => 'Скрытая категория']);

        $response = $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Блог')
            ->assertSee($published->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($future->title)
            ->assertDontSee($inactive->title)
            ->assertSee('Жизнь посёлка');

        $this->assertTrue($response->viewData('posts')->every(
            fn (BlogPost $post): bool => $post->relationLoaded('category') && ! array_key_exists('content', $post->getAttributes()),
        ));
    }

    public function test_only_publicly_visible_article_slugs_are_available(): void
    {
        $category = BlogCategory::factory()->create();
        $published = BlogPost::factory()->for($category, 'category')->create(['slug' => 'published-post']);
        $draft = BlogPost::factory()->for($category, 'category')->draft()->create(['slug' => 'draft-post']);
        $future = BlogPost::factory()->for($category, 'category')->future()->create(['slug' => 'future-post']);
        $inactive = BlogPost::factory()->for(BlogCategory::factory()->inactive(), 'category')->create(['slug' => 'inactive-category-post']);

        $this->get(route('blog.show', $published->slug))->assertOk()->assertSee($published->title);
        $this->get(route('blog.show', $draft->slug))->assertNotFound();
        $this->get(route('blog.show', $future->slug))->assertNotFound();
        $this->get(route('blog.show', $inactive->slug))->assertNotFound();
    }

    public function test_category_search_date_and_sort_filters_work_together(): void
    {
        $life = BlogCategory::factory()->create(['slug' => 'life']);
        $advice = BlogCategory::factory()->create(['slug' => 'advice']);
        $match = BlogPost::factory()->for($life, 'category')->create([
            'title' => 'Лес рядом с домом',
            'excerpt' => 'Прогулка по лесу',
            'published_at' => '2026-07-15 10:00:00',
        ]);
        $wrongCategory = BlogPost::factory()->for($advice, 'category')->create([
            'title' => 'Лес и советы',
            'published_at' => '2026-07-10 10:00:00',
        ]);
        $wrongMonth = BlogPost::factory()->for($life, 'category')->create([
            'title' => 'Зимний лес',
            'published_at' => '2026-01-10 10:00:00',
        ]);

        $this->travelTo('2026-08-11 12:00:00');

        $this->get(route('blog.index', ['category' => 'life', 'q' => 'лес', 'date' => '2026-07', 'sort' => 'oldest']))
            ->assertOk()
            ->assertSee($match->title)
            ->assertDontSee($wrongCategory->title)
            ->assertDontSee($wrongMonth->title);

        $this->get(route('blog.index', ['category' => 'missing']))
            ->assertOk()
            ->assertSee('Ничего не найдено');
    }

    public function test_pagination_preserves_filters(): void
    {
        config()->set('blog.per_page', 2);
        $category = BlogCategory::factory()->create(['slug' => 'life']);
        BlogPost::factory()->count(3)->for($category, 'category')->create([
            'excerpt' => 'forest notes',
        ]);

        $this->get(route('blog.index', ['category' => 'life', 'q' => 'forest']))
            ->assertOk()
            ->assertSee('page=2', false)
            ->assertSee('category=life', false)
            ->assertSee('q=forest', false);
    }

    public function test_previous_and_next_use_stable_publication_order_and_exclude_hidden_posts(): void
    {
        $category = BlogCategory::factory()->create();
        $older = BlogPost::factory()->for($category, 'category')->create([
            'title' => 'Предыдущая видимая',
            'published_at' => now()->subDays(3),
        ]);
        $current = BlogPost::factory()->for($category, 'category')->create([
            'title' => 'Текущая статья',
            'published_at' => now()->subDays(2),
        ]);
        $newer = BlogPost::factory()->for($category, 'category')->create([
            'title' => 'Следующая видимая',
            'published_at' => now()->subDay(),
        ]);
        $draft = BlogPost::factory()->for($category, 'category')->draft()->create(['title' => 'Скрытый черновик']);
        $future = BlogPost::factory()->for($category, 'category')->future()->create(['title' => 'Скрытое будущее']);

        $this->get(route('blog.show', $current->slug))
            ->assertOk()
            ->assertSee($older->title)
            ->assertSee($newer->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($future->title);
    }

    public function test_article_blocks_are_sanitized_and_rendered_without_xss(): void
    {
        $post = BlogPost::factory()->create([
            'content' => [
                ['type' => 'heading', 'data' => ['text' => '<script>bad()</script>Безопасный заголовок', 'level' => 9]],
                ['type' => 'rich_text', 'data' => ['html' => '<p onclick="bad()">Текст</p><script>alert(1)</script><a href="javascript:bad()">ссылка</a>']],
                ['type' => 'list', 'data' => ['style' => 'ordered', 'items' => [['text' => '<b>Первый</b>']]]],
                ['type' => 'image', 'data' => ['path' => '../secret.jpg', 'alt' => 'bad']],
                ['type' => 'unknown', 'data' => ['html' => '<p>unknown</p>']],
            ],
        ])->refresh();

        $serialized = json_encode($post->content);
        $this->assertStringNotContainsString('script', $serialized);
        $this->assertStringNotContainsString('onclick', $serialized);
        $this->assertStringNotContainsString('javascript:', $serialized);
        $this->assertStringNotContainsString('../secret.jpg', $serialized);
        $this->assertCount(3, $post->content);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('Безопасный заголовок')
            ->assertSee('Первый')
            ->assertDontSee('alert(1)', false);
    }

    public function test_article_uses_seo_fallbacks_canonical_and_open_graph(): void
    {
        $post = BlogPost::factory()->create([
            'title' => 'Обычный заголовок',
            'seo_title' => 'SEO заголовок',
            'seo_description' => 'SEO описание',
        ]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('<title>SEO заголовок</title>', false)
            ->assertSee('rel="canonical" href="'.route('blog.show', $post->slug).'"', false)
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('SEO описание');
    }

    public function test_structured_image_blocks_preserve_alt_and_caption(): void
    {
        $post = BlogPost::factory()->create([
            'content' => [
                ['type' => 'image', 'data' => ['path' => 'blog/content/single.webp', 'alt' => 'Один дом', 'caption' => 'Одиночное изображение']],
                ['type' => 'wide_image', 'data' => ['path' => 'blog/content/wide.webp', 'alt' => 'Панорама', 'caption' => 'Широкое изображение']],
                ['type' => 'gallery', 'data' => ['images' => [
                    ['path' => 'blog/content/first.webp', 'alt' => 'Первый кадр', 'caption' => null],
                    ['path' => 'blog/content/second.webp', 'alt' => 'Второй кадр', 'caption' => 'Подпись галереи'],
                ]]],
            ],
        ]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('alt="Один дом"', false)
            ->assertSee('alt="Панорама"', false)
            ->assertSee('alt="Первый кадр"', false)
            ->assertSee('alt="Второй кадр"', false)
            ->assertSee('Подпись галереи');
    }
}
