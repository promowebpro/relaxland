<?php

namespace App\Domain\Content;

enum LegalDocumentType: string
{
    case PrivacyPolicy = 'privacy-policy';
    case PersonalDataConsent = 'personal-data-consent';
    case PublicOffer = 'public-offer';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PrivacyPolicy => 'Политика обработки персональных данных',
            self::PersonalDataConsent => 'Согласие на обработку персональных данных',
            self::PublicOffer => 'Договор публичной оферты',
            self::Other => 'Другой документ',
        };
    }
}
