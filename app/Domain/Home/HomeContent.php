<?php

namespace App\Domain\Home;

class HomeContent
{
    /** @var array<string, list<string>> */
    private const COLLECTION_FIELDS = [
        'benefits' => ['title', 'text'],
        'life_scenarios' => ['label', 'title', 'text', 'image', 'image_mobile', 'image_alt'],
        'care_items' => ['title', 'text', 'note', 'image', 'image_mobile', 'image_alt', 'image_preset', 'icon'],
        'seasons' => ['label', 'title', 'text', 'image', 'image_mobile', 'image_alt'],
        'purchase_options' => ['title', 'text'],
    ];

    /**
     * Keep the fixed Home collections predictable and plain-text only.
     *
     * @param  array<int, mixed>|null  $items
     * @return list<array<string, string>>
     */
    public function sanitize(string $collection, ?array $items): array
    {
        $fields = self::COLLECTION_FIELDS[$collection] ?? [];

        return collect($items ?? [])
            ->filter(fn (mixed $item): bool => is_array($item))
            ->take(12)
            ->map(function (array $item) use ($fields): array {
                $clean = [];

                foreach ($fields as $field) {
                    $value = trim(strip_tags((string) ($item[$field] ?? '')));

                    if ($value !== '') {
                        $clean[$field] = mb_substr($value, 0, $field === 'text' ? 2000 : 500);
                    }
                }

                return $clean;
            })
            ->filter(fn (array $item): bool => $item !== [])
            ->values()
            ->all();
    }
}
