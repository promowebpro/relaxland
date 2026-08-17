<?php

namespace Tests\Feature\ReleaseSeven;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\Plot;
use App\Domain\Genplan\PlotStatus;
use App\Domain\Genplan\Quarter;
use App\Domain\Leads\Lead;
use App\Domain\Leads\LeadStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PlotLeadContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        RateLimiter::clear('127.0.0.1');
    }

    public function test_available_plot_consultation_stores_server_resolved_safe_context(): void
    {
        [$quarter, $plot] = $this->fixture();

        $this->post(route('leads.store'), $this->payload([
            'quarter' => $quarter->slug,
            'plot' => $plot->slug,
            'message' => 'Позвоните после 18:00.',
        ]))->assertRedirect(route('success'));

        $lead = Lead::query()->sole();
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertStringContainsString('Генплан: Публичный генплан; квартал: Северный (north); участок №1 (n-1).', $lead->message);
        $this->assertStringContainsString('Позвоните после 18:00.', $lead->message);
        $this->assertStringNotContainsString('777777', $lead->message);
        $this->assertStringNotContainsString('available', $lead->message);
    }

    public function test_unrelated_or_hidden_plot_context_is_ignored(): void
    {
        [$quarter] = $this->fixture();
        $otherQuarter = Quarter::factory()->create(['genplan_id' => $quarter->genplan_id, 'slug' => 'south']);
        $foreign = Plot::factory()->create(['quarter_id' => $otherQuarter->id, 'slug' => 's-1']);

        $this->post(route('leads.store'), $this->payload([
            'quarter' => $quarter->slug,
            'plot' => $foreign->slug,
            'message' => 'Обычный вопрос.',
        ]))->assertRedirect(route('success'));

        $this->assertSame('Обычный вопрос.', Lead::query()->sole()->message);
    }

    public function test_reserved_and_sold_plot_cards_have_no_inquiry_cta(): void
    {
        [$quarter] = $this->fixture();
        $reserved = Plot::factory()->create(['quarter_id' => $quarter->id, 'number' => '2', 'slug' => 'n-2', 'status' => PlotStatus::Reserved]);

        $this->get(route('genplan.index', ['quarter' => $quarter->slug, 'plot' => $reserved->slug]))
            ->assertOk()
            ->assertSee('Забронирован')
            ->assertDontSee('data-plot-card-cta', false);
    }

    /** @return array{Quarter, Plot} */
    private function fixture(): array
    {
        $genplan = Genplan::factory()->create(['name' => 'Публичный генплан']);
        $quarter = Quarter::factory()->create(['genplan_id' => $genplan->id, 'name' => 'Северный', 'slug' => 'north']);
        $plot = Plot::factory()->create([
            'quarter_id' => $quarter->id,
            'number' => '1',
            'slug' => 'n-1',
            'price' => '777777.00',
            'status' => PlotStatus::Available,
        ]);

        return [$quarter, $plot];
    }

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Анна',
            'phone' => '+7 (900) 123-45-67',
            'email' => 'anna@example.test',
            'message' => null,
            'source' => 'genplan-preview',
            'form_type' => 'consultation',
            'page_url' => 'http://localhost/genplan',
            'consent' => '1',
            'website' => '',
        ], $overrides);
    }
}
