<?php

namespace App\Domain\Seo;

final class CanonicalUrl
{
    public function origin(): string
    {
        $configured = trim((string) config('seo.public_url'));
        $parts = parse_url($configured);

        if ($parts === false || ! in_array($parts['scheme'] ?? null, ['http', 'https'], true) || empty($parts['host'])) {
            return 'http://localhost';
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return strtolower($parts['scheme']).'://'.strtolower($parts['host']).$port;
    }

    public function to(string $path = '/'): string
    {
        $pathOnly = parse_url($path, PHP_URL_PATH);
        $normalized = is_string($pathOnly) && $pathOnly !== '' ? '/'.ltrim($pathOnly, '/') : '/';

        return $this->origin().($normalized === '/' ? '' : $normalized);
    }
}
