<?php

namespace Tests\Feature\ReleaseOne;

use App\Domain\Content\LegalContentSanitizer;
use App\Domain\Settings\SettingsRepository;
use App\Models\LegalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_simple_public_pages_are_available(): void
    {
        $contacts = $this->get(route('contacts'))
            ->assertOk()
            ->assertSee('Контакты');

        $contacts
            ->assertDontSee('/css/filament', false)
            ->assertDontSee('/js/filament', false);

        $this->get(route('legal.index'))
            ->assertOk()
            ->assertSee('Конфиденциальность');

        $this->get(route('success'))
            ->assertOk()
            ->assertSee('Спасибо!');

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('О нас')
            ->assertSee('Забота о клиенте');
    }

    public function test_release_five_does_not_expose_future_product_routes(): void
    {
        foreach (['/plots', '/surroundings'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    public function test_unknown_url_returns_custom_404_with_http_404_status(): void
    {
        $this->get('/definitely-missing-page')
            ->assertNotFound()
            ->assertSee('Ошибка 404')
            ->assertSee('Такой страницы нет')
            ->assertSee('error-404-art.png')
            ->assertSee('noindex, nofollow');
    }

    public function test_only_active_and_published_legal_documents_are_public(): void
    {
        $visible = LegalDocument::factory()->create([
            'title' => 'Действующий документ',
            'slug' => 'active-document',
        ]);
        $inactive = LegalDocument::factory()->inactive()->create([
            'title' => 'Скрытый документ',
            'slug' => 'inactive-document',
        ]);
        $future = LegalDocument::factory()->create([
            'title' => 'Будущий документ',
            'slug' => 'future-document',
            'published_at' => now()->addDay(),
        ]);
        $pdf = LegalDocument::factory()->create([
            'title' => 'Документ в PDF',
            'slug' => 'pdf-document',
            'content' => null,
            'file_path' => 'legal-documents/sample.pdf',
        ]);

        $this->get(route('legal.index'))
            ->assertOk()
            ->assertSee($visible->title)
            ->assertDontSee($inactive->title)
            ->assertDontSee($future->title);

        $this->get(route('legal.show', $visible->slug))->assertOk();
        $this->get(route('legal.show', $pdf->slug))
            ->assertOk()
            ->assertSee('Открыть PDF');
        $this->get(route('legal.show', $inactive->slug))->assertNotFound();
        $this->get(route('legal.show', $future->slug))->assertNotFound();
    }

    public function test_public_settings_are_rendered_in_contacts_header_and_footer(): void
    {
        $settings = app(SettingsRepository::class);
        $settings->set('contacts.phone', '+7 4012 555-010', 'contacts', true);
        $settings->set('contacts.email', 'hello@relaxland.test', 'contacts', true);
        $settings->set('contacts.working_hours', 'Ежедневно 09:00–20:00', 'contacts', true);
        $settings->set('contacts.village_address', 'Тестовый адрес посёлка', 'contacts', true);
        $settings->set('footer.copyright', '© RelaxLand Test', 'footer', true);

        $this->get(route('contacts'))
            ->assertOk()
            ->assertSee('+7 4012 555-010')
            ->assertSee('hello@relaxland.test')
            ->assertSee('Ежедневно 09:00–20:00')
            ->assertSee('Тестовый адрес посёлка')
            ->assertSee('© RelaxLand Test');
    }

    public function test_contacts_use_configured_live_map_and_contextual_invitation(): void
    {
        $settings = app(SettingsRepository::class);
        $settings->set('contacts.village_latitude', 55.8, 'contacts', true);
        $settings->set('contacts.village_longitude', 36.4, 'contacts', true);
        $settings->set('routes.yandex', 'https://yandex.ru/maps/?test=route', 'routes', true);

        $this->get(route('contacts'))->assertOk()
            ->assertSee('data-latitude="55.8"', false)
            ->assertSee('data-longitude="36.4"', false)
            ->assertSee('https://yandex.ru/maps/?test=route', false)
            ->assertSee('data-lead-source="contacts"', false)
            ->assertSee('about-hedgehog.svg')
            ->assertDontSee('contacts-map.webp');
    }

    public function test_legal_html_is_sanitized_on_write_and_render(): void
    {
        $document = LegalDocument::factory()->create([
            'slug' => 'safe-document',
            'content' => '<h2 onclick="alert(1)">Заголовок</h2><script>alert(2)</script><a href="javascript:alert(3)">Ссылка</a>',
        ]);

        $this->assertStringNotContainsString('script', $document->content);
        $this->assertStringNotContainsString('onclick', $document->content);
        $this->assertStringNotContainsString('javascript:', $document->content);
        $this->assertSame($document->content, app(LegalContentSanitizer::class)->sanitize($document->content));

        $this->get(route('legal.show', $document->slug))
            ->assertOk()
            ->assertSee('Заголовок')
            ->assertDontSee('alert(2)', false);
    }
}
