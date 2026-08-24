<?php

namespace Tests\Feature\ReleaseNine;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RobotsSitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('seo.public_url', 'https://relaxland.test');
    }

    public function test_production_robots_allows_public_content_and_blocks_technical_routes(): void
    {
        config()->set('seo.indexing_enabled', true);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Allow: /', false)
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /api', false)
            ->assertSee('Disallow: /thanks', false)
            ->assertSee('Disallow: /up', false)
            ->assertSee('Sitemap: https://relaxland.test/sitemap.xml', false);
    }

    public function test_non_production_robots_disallows_everything_without_host_guessing(): void
    {
        config()->set('seo.indexing_enabled', false);

        $this->withServerVariables(['HTTP_HOST' => 'production-looking.example'])
            ->get('/robots.txt')
            ->assertOk()
            ->assertSee("User-agent: *\nDisallow: /\n", false)
            ->assertDontSee('Sitemap:', false);
    }

    public function test_sitemap_contains_only_canonical_public_routes_and_published_articles(): void
    {
        $public = BlogPost::factory()->create(['slug' => 'public-article']);
        $draft = BlogPost::factory()->draft()->create(['slug' => 'draft-article']);
        $future = BlogPost::factory()->future()->create(['slug' => 'future-article']);

        $response = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>https://relaxland.test</loc>', false)
            ->assertSee('<loc>https://relaxland.test/about</loc>', false)
            ->assertSee('<loc>https://relaxland.test/contacts</loc>', false)
            ->assertSee('<loc>https://relaxland.test/blog</loc>', false)
            ->assertSee('<loc>https://relaxland.test/genplan</loc>', false)
            ->assertSee('/blog/'.$public->slug, false)
            ->assertDontSee($draft->slug, false)
            ->assertDontSee($future->slug, false)
            ->assertDontSee('/admin', false)
            ->assertDontSee('/api', false)
            ->assertDontSee('/thanks', false)
            ->assertDontSee('/privacy', false)
            ->assertDontSee('?', false);

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $this->assertCount(6, $xml->url);
    }

    public function test_sitemap_order_is_deterministic_and_query_count_is_bounded(): void
    {
        BlogPost::factory()->create(['slug' => 'z-last']);
        BlogPost::factory()->create(['slug' => 'a-first']);
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $first = $this->get('/sitemap.xml')->assertOk()->getContent();
        $second = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertSame($first, $second);
        $this->assertLessThan(strpos($first, 'z-last'), strpos($first, 'a-first'));
        $this->assertLessThanOrEqual(2, collect($queries)->filter(fn (string $sql): bool => str_contains($sql, 'blog_posts'))->count());
    }
}
