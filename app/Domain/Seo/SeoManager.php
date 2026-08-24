<?php

namespace App\Domain\Seo;

use App\Domain\Settings\SiteSettings;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class SeoManager
{
    public function __construct(
        private readonly SiteSettings $siteSettings,
        private readonly CanonicalUrl $canonicalUrl,
    ) {}

    /**
     * @param  list<array{label: string, url?: string}>  $breadcrumbs
     */
    public function forPage(
        string $path,
        ?string $seoTitle = null,
        ?string $entityTitle = null,
        ?string $routeTitle = null,
        ?string $seoDescription = null,
        ?string $summary = null,
        bool $indexable = true,
        bool $follow = true,
        string $ogType = 'website',
        ?string $ogImagePath = null,
        array $breadcrumbs = [],
        ?BlogPost $article = null,
        bool $includeGlobalStructuredData = true,
        ?array $settings = null,
    ): SeoMetadata {
        $settings ??= $this->siteSettings->all();
        $siteName = $this->cleanText($settings['seo.site_title'] ?? null, 70)
            ?: $this->cleanText(config('app.name'), 70)
            ?: 'RelaxLand';
        $suffix = $this->cleanText($settings['seo.title_suffix'] ?? null, 50);
        $title = $this->title(
            $this->cleanText($seoTitle, 70)
                ?: $this->cleanText($entityTitle, 70)
                ?: $this->cleanText($routeTitle, 70)
                ?: $siteName,
            $siteName,
            $suffix,
        );
        $description = $this->cleanText($seoDescription, 170)
            ?: $this->cleanText($summary, 170)
            ?: $this->cleanText($settings['seo.default_description'] ?? null, 170)
            ?: 'RelaxLand Можайский';
        $canonical = $this->canonicalUrl->to($path);
        $locale = $this->locale($settings['seo.default_locale'] ?? null);
        $robots = config('seo.indexing_enabled') && $indexable
            ? 'index, follow'
            : 'noindex, '.($follow ? 'follow' : 'nofollow');
        $ogImage = $this->storageImageUrl($ogImagePath)
            ?: $this->storageImageUrl($settings['seo.default_og_image'] ?? null);
        $normalizedBreadcrumbs = $this->breadcrumbs($breadcrumbs, $canonical);
        $structuredData = $includeGlobalStructuredData
            ? $this->globalStructuredData($settings, $siteName, $locale)
            : [];

        if ($normalizedBreadcrumbs !== []) {
            $structuredData[] = $this->breadcrumbSchema($normalizedBreadcrumbs);
        }

        if ($article) {
            $structuredData[] = $this->articleSchema($article, $description, $canonical, $ogImage, $locale);
        }

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: $canonical,
            robots: $robots,
            ogType: $ogType,
            ogImage: $ogImage,
            siteName: $siteName,
            locale: $locale,
            breadcrumbs: $normalizedBreadcrumbs,
            structuredData: $structuredData,
            articlePublishedTime: $article?->published_at?->toAtomString(),
            articleModifiedTime: $article?->updated_at?->toAtomString(),
        );
    }

    public function error(int $status): SeoMetadata
    {
        $title = $status === 404 ? 'Страница не найдена' : 'Ошибка сервера';

        return new SeoMetadata(
            title: $title,
            description: 'Запрошенная страница недоступна.',
            canonical: null,
            robots: 'noindex, nofollow',
            ogType: 'website',
            ogImage: null,
            siteName: $this->cleanText(config('app.name'), 70) ?: 'RelaxLand',
            locale: (string) config('seo.default_locale', 'ru_RU'),
        );
    }

    private function title(string $title, string $siteName, ?string $suffix): string
    {
        if (! $suffix || $title === $siteName || Str::endsWith(Str::lower($title), Str::lower($suffix))) {
            return $title;
        }

        return $this->cleanText($title.' — '.$suffix, 70) ?: $title;
    }

    private function cleanText(mixed $value, int $limit): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        if ($value === '') {
            return null;
        }

        return Str::limit($value, $limit, '');
    }

    private function locale(mixed $value): string
    {
        $locale = is_string($value) ? trim($value) : '';

        return preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale) ? $locale : (string) config('seo.default_locale', 'ru_RU');
    }

    private function storageImageUrl(mixed $path): ?string
    {
        if (! is_string($path) || $path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        return $this->canonicalUrl->to('/storage/'.ltrim($path, '/'));
    }

    /**
     * @param  list<array{label: string, url?: string}>  $breadcrumbs
     * @return list<array{label: string, url?: string}>
     */
    private function breadcrumbs(array $breadcrumbs, string $canonical): array
    {
        return collect($breadcrumbs)
            ->map(function (array $item, int $index) use ($breadcrumbs, $canonical): ?array {
                $label = $this->cleanText($item['label'] ?? null, 150);

                if (! $label) {
                    return null;
                }

                $url = $item['url'] ?? null;

                if ($index === array_key_last($breadcrumbs)) {
                    $url = $canonical;
                } elseif (is_string($url)) {
                    $url = $this->canonicalUrl->to($url);
                }

                return $url ? ['label' => $label, 'url' => $url] : ['label' => $label];
            })
            ->filter()
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $settings */
    private function globalStructuredData(array $settings, string $siteName, string $locale): array
    {
        $origin = $this->canonicalUrl->origin();
        $organizationName = $this->cleanText($settings['seo.organization_name'] ?? null, 150) ?: $siteName;
        $organization = [
            '@type' => 'Organization',
            '@id' => $origin.'/#organization',
            'name' => $organizationName,
            'url' => $origin,
        ];
        $sameAs = collect(['social.telegram', 'social.vk', 'social.whatsapp', 'social.max'])
            ->map(fn (string $key): mixed => $settings[$key] ?? null)
            ->filter(fn (mixed $url): bool => is_string($url) && str_starts_with($url, 'https://'))
            ->values()
            ->all();

        if ($sameAs !== []) {
            $organization['sameAs'] = $sameAs;
        }

        $phone = $this->cleanText($settings['contacts.sales_phone'] ?? $settings['contacts.phone'] ?? null, 100);
        $email = filter_var($settings['contacts.email'] ?? null, FILTER_VALIDATE_EMAIL) ?: null;

        if ($phone || $email) {
            $organization['contactPoint'] = array_filter([
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'telephone' => $phone,
                'email' => $email,
            ]);
        }

        $address = $this->cleanText($settings['contacts.office_address'] ?? $settings['contacts.village_address'] ?? null, 300);

        if ($address) {
            $organization['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
            ];
        }

        return [
            $organization,
            [
                '@type' => 'WebSite',
                '@id' => $origin.'/#website',
                'url' => $origin,
                'name' => $siteName,
                'inLanguage' => str_replace('_', '-', $locale),
                'publisher' => ['@id' => $origin.'/#organization'],
            ],
        ];
    }

    /** @param list<array{label: string, url?: string}> $breadcrumbs */
    private function breadcrumbSchema(array $breadcrumbs): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)->values()->map(
                fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['label'],
                    'item' => $item['url'] ?? null,
                ],
            )->all(),
        ];
    }

    private function articleSchema(BlogPost $article, string $description, string $canonical, ?string $image, string $locale): array
    {
        return array_filter([
            '@type' => 'Article',
            '@id' => $canonical.'#article',
            'headline' => $this->cleanText($article->title, 110),
            'description' => $description,
            'datePublished' => $article->published_at?->toAtomString(),
            'dateModified' => $article->updated_at?->toAtomString(),
            'mainEntityOfPage' => $canonical,
            'image' => $image,
            'inLanguage' => str_replace('_', '-', $locale),
            'publisher' => ['@id' => $this->canonicalUrl->origin().'/#organization'],
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
