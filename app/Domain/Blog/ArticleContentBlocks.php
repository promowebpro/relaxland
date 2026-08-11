<?php

namespace App\Domain\Blog;

use App\Domain\Content\HtmlSanitizer;

class ArticleContentBlocks
{
    public function __construct(private readonly HtmlSanitizer $htmlSanitizer) {}

    /**
     * @param  array<int, mixed>|null  $blocks
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    public function sanitize(?array $blocks): array
    {
        $sanitized = [];

        foreach ($blocks ?? [] as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = ArticleBlockType::tryFrom((string) ($block['type'] ?? ''));
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            if (! $type) {
                continue;
            }

            $clean = match ($type) {
                ArticleBlockType::Heading => $this->sanitizeHeading($data),
                ArticleBlockType::RichText => $this->sanitizeRichText($data),
                ArticleBlockType::List => $this->sanitizeList($data),
                ArticleBlockType::Image, ArticleBlockType::WideImage => $this->sanitizeImage($data),
                ArticleBlockType::Gallery => $this->sanitizeGallery($data),
            };

            if ($clean !== null) {
                $sanitized[] = ['type' => $type->value, 'data' => $clean];
            }
        }

        return $sanitized;
    }

    /** @param array<string, mixed> $data */
    private function sanitizeHeading(array $data): ?array
    {
        $text = $this->plainText($data['text'] ?? null, 500);

        return $text === null ? null : [
            'text' => $text,
            'level' => in_array((int) ($data['level'] ?? 2), [2, 3], true) ? (int) $data['level'] : 2,
        ];
    }

    /** @param array<string, mixed> $data */
    private function sanitizeRichText(array $data): ?array
    {
        $html = $this->htmlSanitizer->sanitize(is_string($data['html'] ?? null) ? $data['html'] : null);

        return $html === null ? null : ['html' => $html];
    }

    /** @param array<string, mixed> $data */
    private function sanitizeList(array $data): ?array
    {
        $items = collect(is_array($data['items'] ?? null) ? $data['items'] : [])
            ->map(fn (mixed $item): ?string => $this->plainText(is_array($item) ? ($item['text'] ?? null) : $item, 1000))
            ->filter()
            ->values()
            ->take(50)
            ->all();

        return $items === [] ? null : [
            'style' => ($data['style'] ?? null) === 'ordered' ? 'ordered' : 'unordered',
            'items' => $items,
        ];
    }

    /** @param array<string, mixed> $data */
    private function sanitizeImage(array $data): ?array
    {
        $path = $this->storagePath($data['path'] ?? null);

        if ($path === null) {
            return null;
        }

        return [
            'path' => $path,
            'alt' => $this->plainText($data['alt'] ?? null, 500) ?? '',
            'caption' => $this->plainText($data['caption'] ?? null, 1000),
        ];
    }

    /** @param array<string, mixed> $data */
    private function sanitizeGallery(array $data): ?array
    {
        $images = collect(is_array($data['images'] ?? null) ? $data['images'] : [])
            ->map(fn (mixed $image): ?array => is_array($image) ? $this->sanitizeImage($image) : null)
            ->filter()
            ->values()
            ->take(4)
            ->all();

        return $images === [] ? null : ['images' => $images];
    }

    private function plainText(mixed $value, int $maxLength): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');

        return $value === '' ? null : mb_substr($value, 0, $maxLength);
    }

    private function storagePath(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', trim($value)), '/');

        if ($path === '' || str_contains($path, '..') || ! str_starts_with($path, 'blog/')) {
            return null;
        }

        return mb_substr($path, 0, 2048);
    }
}
