<?php

namespace Tests\Feature\ReleaseFour;

use App\Domain\Content\LegalDocumentType;
use App\Domain\Leads\Lead;
use App\Domain\Leads\LeadFormType;
use App\Domain\Leads\LeadSource;
use App\Domain\Leads\LeadStatus;
use App\Models\LegalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PublicLeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        RateLimiter::clear('127.0.0.1');
    }

    public function test_valid_lead_is_created_with_consent_snapshot_and_redirects_to_thanks(): void
    {
        $document = LegalDocument::factory()->create([
            'type' => LegalDocumentType::PersonalDataConsent,
            'version' => '2026-08-11',
        ]);

        $response = $this->post(route('leads.store'), $this->validPayload());

        $response->assertRedirect(route('success'));
        $lead = Lead::query()->sole();
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertSame($document->id, $lead->privacy_document_id);
        $this->assertSame('2026-08-11', $lead->privacy_document_version);
        $this->assertNotNull($lead->consent_given_at);
        $this->get('/leads')->assertMethodNotAllowed();
    }

    public function test_phone_and_consent_are_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['phone'], $payload['consent']);

        $this->from(route('contacts'))
            ->post(route('leads.store'), $payload)
            ->assertRedirect(route('contacts'))
            ->assertSessionHasErrors(['phone', 'consent'], errorBag: 'lead');

        $this->assertDatabaseEmpty('leads');
    }

    public function test_invalid_email_source_and_form_type_are_rejected(): void
    {
        $this->post(route('leads.store'), $this->validPayload([
            'email' => 'not-an-email',
            'source' => 'arbitrary-source',
            'form_type' => 'arbitrary-form',
        ]))->assertSessionHasErrors(['email', 'source', 'form_type'], errorBag: 'lead');

        $this->assertDatabaseEmpty('leads');
    }

    public function test_visit_and_consultation_require_a_name_but_callback_does_not(): void
    {
        $this->post(route('leads.store'), $this->validPayload([
            'name' => '',
            'form_type' => LeadFormType::Visit->value,
        ]))->assertSessionHasErrors('name', errorBag: 'lead');

        $this->post(route('leads.store'), $this->validPayload([
            'name' => '',
            'form_type' => LeadFormType::Callback->value,
        ]))->assertRedirect(route('success'));

        $this->assertDatabaseCount('leads', 1);
        $this->assertNull(Lead::query()->sole()->name);
    }

    public function test_phone_is_normalized_deterministically(): void
    {
        $this->post(route('leads.store'), $this->validPayload([
            'phone' => '8 (900) 123-45-67',
        ]))->assertRedirect(route('success'));

        $this->assertSame('+79001234567', Lead::query()->sole()->phone);
    }

    public function test_session_utm_uses_first_non_empty_touch_and_is_not_overwritten(): void
    {
        $this->get('/?utm_source=yandex&utm_campaign=summer&utm_medium=cpc')->assertOk();
        $this->get('/about?utm_source=google&utm_campaign=autumn&utm_content=about-card')->assertOk();

        $this->post(route('leads.store'), $this->validPayload())->assertRedirect(route('success'));

        $lead = Lead::query()->sole();
        $this->assertSame('yandex', $lead->utm_source);
        $this->assertSame('cpc', $lead->utm_medium);
        $this->assertSame('summer', $lead->utm_campaign);
        $this->assertSame('about-card', $lead->utm_content);
        $this->assertNull($lead->utm_term);
    }

    public function test_page_url_is_limited_to_the_current_host_and_query_is_removed(): void
    {
        $this->post(route('leads.store'), $this->validPayload([
            'page_url' => 'http://localhost/about?secret=value',
        ]));

        $this->assertSame('http://localhost/about', Lead::query()->sole()->page_url);

        $this->post(route('leads.store'), $this->validPayload([
            'page_url' => 'https://evil.example/collect',
        ]));

        $this->assertNull(Lead::query()->latest('id')->firstOrFail()->page_url);
    }

    public function test_honeypot_rejects_bots_without_storing_a_lead(): void
    {
        $this->post(route('leads.store'), $this->validPayload(['website' => 'spam.example']))
            ->assertSessionHasErrors('website', errorBag: 'lead');

        $this->assertDatabaseEmpty('leads');
    }

    public function test_rate_limit_allows_normal_use_and_blocks_the_sixth_submission(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(route('leads.store'), $this->validPayload([
                'phone' => '+7 900 000-00-0'.$attempt,
            ]))->assertRedirect(route('success'));
        }

        $this->post(route('leads.store'), $this->validPayload(['phone' => '+7 900 000-00-06']))
            ->assertTooManyRequests();
        $this->assertDatabaseCount('leads', 5);
    }

    public function test_public_pages_expose_one_reusable_lead_form_and_contextual_ctas(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('action="'.route('leads.store').'"', false)
            ->assertSee('data-lead-source="home"', false)
            ->assertSee('data-lead-form-type="visit"', false);

        $this->get(route('contacts'))
            ->assertOk()
            ->assertSee('data-lead-source="contacts"', false)
            ->assertSee('Записаться');

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('data-lead-source="about"', false);
    }

    public function test_repeated_submissions_are_not_blocked_as_duplicates(): void
    {
        $payload = $this->validPayload();

        $this->post(route('leads.store'), $payload)->assertRedirect(route('success'));
        $this->post(route('leads.store'), $payload)->assertRedirect(route('success'));

        $this->assertDatabaseCount('leads', 2);
    }

    public function test_message_is_rendered_as_text_in_public_validation_flow(): void
    {
        $this->from(route('contacts'))
            ->post(route('leads.store'), $this->validPayload([
                'phone' => '',
                'message' => '<img src=x onerror=alert("lead")>',
            ]))
            ->assertRedirect(route('contacts'));

        $this->get(route('contacts'))
            ->assertOk()
            ->assertDontSee('<img src=x onerror=alert("lead")>', false)
            ->assertSee('&lt;img src=x onerror=alert(&quot;lead&quot;)&gt;', false);
    }

    /** @param array<string, mixed> $overrides */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Анна',
            'phone' => '+7 (900) 123-45-67',
            'email' => 'anna@example.test',
            'message' => 'Хочу приехать на экскурсию.',
            'source' => LeadSource::Contacts->value,
            'form_type' => LeadFormType::Visit->value,
            'page_url' => 'http://localhost/contacts',
            'consent' => '1',
            'website' => '',
        ], $overrides);
    }
}
