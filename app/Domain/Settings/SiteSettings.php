<?php

namespace App\Domain\Settings;

use App\Domain\Genplan\GeographicPoint;

class SiteSettings
{
    /**
     * @var list<string>
     */
    public const KEYS = [
        'contacts.phone',
        'contacts.sales_phone',
        'contacts.email',
        'contacts.support_email',
        'contacts.working_hours',
        'contacts.office_address',
        'contacts.village_address',
        'contacts.village_latitude',
        'contacts.village_longitude',
        'contacts.notice',
        'contacts.travel_time',
        'social.telegram',
        'social.vk',
        'social.whatsapp',
        'social.max',
        'routes.yandex',
        'routes.google',
        'routes.two_gis',
        'documents.presentation_url',
        'documents.presentation_file',
        'footer.copyright',
        'footer.disclaimer',
    ];

    public function __construct(private readonly SettingsRepository $settings) {}

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_replace(array_fill_keys(self::KEYS, null), $this->settings->public());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public static function phoneHref(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $normalized = preg_replace('/[^\d+]/u', '', $phone);

        return filled($normalized) ? 'tel:'.$normalized : null;
    }

    public function settlementPoint(): ?GeographicPoint
    {
        $settings = $this->all();

        return GeographicPoint::tryFrom(
            $settings['contacts.village_latitude'],
            $settings['contacts.village_longitude'],
        );
    }
}
