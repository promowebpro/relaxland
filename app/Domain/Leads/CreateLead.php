<?php

namespace App\Domain\Leads;

use Illuminate\Http\Request;

class CreateLead
{
    public function __construct(private readonly LeadConsentDocument $consentDocument) {}

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
            'message' => $data['message'] ?? null,
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
}
