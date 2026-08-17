<?php

namespace App\Domain\Leads;

use App\Domain\Genplan\GenplanMode;
use App\Domain\Genplan\GenplanPublicQuery;
use Illuminate\Http\Request;

class CreateLead
{
    public function __construct(
        private readonly LeadConsentDocument $consentDocument,
        private readonly GenplanPublicQuery $genplanQuery,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, Request $request): Lead
    {
        $document = $this->consentDocument->current();
        $attribution = collect(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'])
            ->mapWithKeys(fn (string $key): array => [$key => $request->session()->get("lead_attribution.{$key}")])
            ->all();

        return Lead::query()->create([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'message' => $this->messageWithPlotContext($data),
            'source' => $data['source'],
            'form_type' => $data['form_type'],
            'page_url' => $data['page_url'] ?? null,
            ...$attribution,
            'status' => LeadStatus::New,
            'consent_given_at' => now(),
            'privacy_document_id' => $document?->getKey(),
            'privacy_document_version' => $document?->version,
        ]);
    }

    /** @param array<string, mixed> $data */
    private function messageWithPlotContext(array $data): ?string
    {
        $message = $data['message'] ?? null;
        $quarterSlug = $data['quarter'] ?? null;
        $plotSlug = $data['plot'] ?? null;

        if (! is_string($quarterSlug) || ! is_string($plotSlug)) {
            return $message;
        }

        $quarter = $this->genplanQuery->quarter($quarterSlug, GenplanMode::default());
        $plot = $quarter ? $this->genplanQuery->plot($quarter, $plotSlug) : null;
        $genplan = $this->genplanQuery->current();

        if (! $genplan || ! $quarter || ! $plot) {
            return $message;
        }

        $context = "Генплан: {$genplan->name}; квартал: {$quarter->name} ({$quarter->slug}); участок №{$plot->number} ({$plot->slug}).";

        return filled($message) ? $context."\n".$message : $context;
    }
}
