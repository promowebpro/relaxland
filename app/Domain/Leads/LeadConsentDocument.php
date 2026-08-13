<?php

namespace App\Domain\Leads;

use App\Domain\Content\LegalDocumentType;
use App\Models\LegalDocument;

class LeadConsentDocument
{
    private bool $resolved = false;

    private ?LegalDocument $document = null;

    public function current(): ?LegalDocument
    {
        if ($this->resolved) {
            return $this->document;
        }

        $this->resolved = true;
        $this->document = LegalDocument::query()
            ->publiclyVisible()
            ->whereIn('type', [
                LegalDocumentType::PersonalDataConsent->value,
                LegalDocumentType::PrivacyPolicy->value,
            ])
            ->orderByRaw('CASE WHEN type = ? THEN 0 ELSE 1 END', [LegalDocumentType::PersonalDataConsent->value])
            ->latest('published_at')
            ->latest('id')
            ->first();

        return $this->document;
    }
}
