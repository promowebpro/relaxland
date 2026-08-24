<?php

namespace Tests\Feature\ReleaseNine;

use App\Domain\Operations\ProductionReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ProductionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_security_headers_are_present_without_premature_hsts_or_csp(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeaderMissing('Strict-Transport-Security')
            ->assertHeaderMissing('Content-Security-Policy');
    }

    public function test_hsts_is_only_sent_when_explicitly_enabled_on_https(): void
    {
        config()->set('operations.hsts_enabled', true);
        config()->set('operations.hsts_max_age', 86400);

        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
        $this->get('https://localhost/')
            ->assertHeader('Strict-Transport-Security', 'max-age=86400; includeSubDomains');
    }

    public function test_api_is_read_only_and_marked_noindex(): void
    {
        $this->getJson('/api/genplan')
            ->assertNotFound()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->postJson('/api/genplan')->assertMethodNotAllowed();
    }

    public function test_health_endpoint_is_minimal_and_marked_noindex(): void
    {
        config()->set('seo.indexing_enabled', true);

        $this->get('/up')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertDontSee('APP_KEY', false)
            ->assertDontSee('DB_PASSWORD', false);
    }

    public function test_production_error_page_does_not_leak_exception_or_filesystem_path(): void
    {
        config()->set('app.debug', false);
        Route::get('/release-nine-error-fixture', fn () => throw new RuntimeException('secret SQL D:\\private\\app.env'));

        $this->get('/release-nine-error-fixture')
            ->assertStatus(500)
            ->assertSee('Сервис временно недоступен')
            ->assertDontSee('secret SQL', false)
            ->assertDontSee('D:\\private', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_production_check_never_prints_key_or_credentials(): void
    {
        config()->set('app.key', 'base64:do-not-print-this-value');
        config()->set('database.connections.mysql.password', 'do-not-print-db-password');

        $this->artisan('app:production-check')
            ->expectsOutputToContain('Application key')
            ->doesntExpectOutputToContain('do-not-print-this-value')
            ->doesntExpectOutputToContain('do-not-print-db-password');
    }

    public function test_production_check_rejects_canonical_origin_with_path(): void
    {
        config()->set('seo.public_url', 'https://relaxland.test/unexpected-path');

        $canonical = collect(app(ProductionReadiness::class)->checks())
            ->firstWhere('check', 'Canonical origin');

        $this->assertSame('BLOCKER', $canonical['status']);
    }
}
