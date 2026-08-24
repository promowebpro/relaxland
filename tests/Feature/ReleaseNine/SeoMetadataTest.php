<?php

namespace Tests\Feature\ReleaseNine;

use App\Domain\Genplan\Genplan;
use App\Domain\Settings\SettingsRepository;
use App\Models\BlogPost;
use App\Models\HomePage;
use App\Models\LegalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config()->set('seo.indexing_enabled', true);
        config()->set('seo.public_url', 'https://relaxland.test');
    }

    public function test_home_uses_published_seo_fields_canonical_open_graph_and_global_schema(): void
    {
        app(SettingsRepository::class)->set('seo.site_title', 'RelaxLand', 'seo', true);
        app(SettingsRepository::class)->set('seo.organization_name', 'ООО РелаксЛэнд', 'seo', true);
        HomePage::query()->create(array_replace(HomePage::defaultContent(), [
            'seo_title' => 'Жизнь у Можайского моря',
            'seo_description' => '  Спокойная   жизнь <b>рядом</b> с природой. ',
        ]));

        $response = $this->get('/')->assertOk();

        $response
            ->assertSee('<title>Жизнь у Можайского моря</title>', false)
            ->assertSee('name="description" content="Спокойная жизнь рядом с природой."', false)
            ->assertSee('name="robots" content="index, follow"', false)
            ->assertSee('rel="canonical" href="https://relaxland.test"', false)
            ->assertSee('property="og:site_name" content="RelaxLand"', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('ООО РелаксЛэнд')
            ->assertSee('"@type":"WebSite"', false);
    }

    public function test_title_suffix_is_not_duplicated(): void
    {
        $settings = app(SettingsRepository::class);
        $settings->set('seo.site_title', 'РелаксЛэнд', 'seo', true);
        $settings->set('seo.title_suffix', 'РелаксЛэнд', 'seo', true);

        $this->get('/about')
            ->assertOk()
            ->assertSee('<title>О нас — РелаксЛэнд</title>', false)
            ->assertDontSee('РелаксЛэнд — РелаксЛэнд', false);
    }

    public function test_global_description_fallback_normalizes_html_and_utf8(): void
    {
        $settings = app(SettingsRepository::class);
        $settings->set('seo.site_title', 'РелаксЛэнд', 'seo', true);
        $settings->set('seo.title_suffix', 'РелаксЛэнд', 'seo', true);
        $settings->set('seo.default_description', '<p>Описание   по умолчанию</p>', 'seo', true);

        HomePage::query()->create(array_replace(HomePage::defaultContent(), [
            'seo_title' => 'РелаксЛэнд',
            'seo_description' => null,
            'hero_description' => null,
        ]));

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>РелаксЛэнд</title>', false)
            ->assertSee('Описание по умолчанию');
    }

    public function test_static_pages_have_fixed_canonical_and_matching_breadcrumb_schema(): void
    {
        foreach (['/about' => 'О нас', '/contacts' => 'Контакты'] as $path => $label) {
            $response = $this->get($path)->assertOk();
            $response
                ->assertSee('rel="canonical" href="https://relaxland.test'.$path.'"', false)
                ->assertSee('"@type":"BreadcrumbList"', false)
                ->assertSee('"name":"'.$label.'"', false);
        }
    }

    public function test_article_uses_override_then_safe_fallback_and_structured_data(): void
    {
        $override = BlogPost::factory()->create([
            'title' => 'Обычный заголовок',
            'seo_title' => 'SEO заголовок',
            'seo_description' => 'SEO описание',
        ]);

        $this->get('/blog/'.$override->slug)
            ->assertOk()
            ->assertSee('<title>SEO заголовок</title>', false)
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('property="article:published_time"', false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"mainEntityOfPage":"https://relaxland.test/blog/'.$override->slug.'"', false);

        $fallback = BlogPost::factory()->create([
            'title' => '<b>Заголовок fallback</b>',
            'excerpt' => '<i>Краткое   описание</i>',
            'seo_title' => null,
            'seo_description' => null,
        ]);

        $this->get('/blog/'.$fallback->slug)
            ->assertOk()
            ->assertSee('<title>Заголовок fallback</title>', false)
            ->assertSee('content="Краткое описание"', false);
    }

    public function test_missing_open_graph_file_omits_broken_image_tag(): void
    {
        $post = BlogPost::factory()->create(['og_image' => 'blog/og/missing.webp']);

        $this->get('/blog/'.$post->slug)
            ->assertOk()
            ->assertDontSee('property="og:image"', false)
            ->assertDontSee('/storage/blog/og/missing.webp', false);
    }

    public function test_tracking_parameters_do_not_poison_canonical_or_disable_primary_page_indexing(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'attacker.invalid'])
            ->get('/blog?utm_source=test&utm_campaign=launch')
            ->assertOk()
            ->assertSee('rel="canonical" href="https://relaxland.test/blog"', false)
            ->assertSee('name="robots" content="index, follow"', false)
            ->assertDontSee('attacker.invalid', false);
    }

    public function test_blog_filters_pagination_unknown_parameters_and_genplan_states_are_noindex(): void
    {
        BlogPost::factory()->count(2)->create();

        foreach (['/blog?q=лес', '/blog?category=missing', '/blog?page=1', '/blog?unknown=value'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('name="robots" content="noindex, follow"', false)
                ->assertSee('rel="canonical" href="https://relaxland.test/blog"', false);
        }

        Genplan::factory()->create();
        $this->get('/genplan?mode=2d&utm_source=test')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, follow"', false)
            ->assertSee('rel="canonical" href="https://relaxland.test/genplan"', false);
    }

    public function test_invalid_and_overflow_pagination_are_controlled_404(): void
    {
        config()->set('blog.per_page', 1);
        BlogPost::factory()->create();

        $this->get('/blog?page[]=1')->assertNotFound();
        $this->get('/blog?page=abc')->assertNotFound();
        $this->get('/blog?page=99')->assertNotFound();
    }

    public function test_legal_thanks_and_error_pages_are_noindex_and_hidden_entities_remain_404(): void
    {
        $active = LegalDocument::factory()->create();
        $inactive = LegalDocument::factory()->inactive()->create();

        $this->get('/privacy')->assertOk()->assertSee('name="robots" content="noindex, follow"', false);
        $this->get('/privacy/'.$active->slug)->assertOk()->assertSee('name="robots" content="noindex, follow"', false);
        $this->get('/thanks')->assertOk()->assertSee('name="robots" content="noindex, nofollow"', false);
        $this->get('/privacy/'.$inactive->slug)->assertNotFound();
        $this->get('/missing-release-nine-page')
            ->assertNotFound()
            ->assertSee('name="robots" content="noindex, nofollow"', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }
}
