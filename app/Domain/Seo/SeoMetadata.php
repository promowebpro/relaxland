<?php

namespace App\Domain\Seo;

final readonly class SeoMetadata
{
    /**
     * @param  list<array{label: string, url?: string}>  $breadcrumbs
     * @param  list<array<string, mixed>>  $structuredData
     */
    public function __construct(
        public string $title,
        public string $description,
        public ?string $canonical,
        public string $robots,
        public string $ogType,
        public ?string $ogImage,
        public string $siteName,
        public string $locale,
        public array $breadcrumbs = [],
        public array $structuredData = [],
        public ?string $articlePublishedTime = null,
        public ?string $articleModifiedTime = null,
    ) {}

    /** @return array<string, mixed>|null */
    public function jsonLd(): ?array
    {
        if ($this->structuredData === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $this->structuredData,
        ];
    }
}
