<?php

namespace App\Domain\Genplan;

final class PublicMapLink
{
    public static function https(mixed $value): ?string
    {
        if (! is_string($value) || $value === '' || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            return null;
        }

        $parts = parse_url($value);

        if ($parts === false
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || blank($parts['host'] ?? null)
            || isset($parts['user'])
            || isset($parts['pass'])) {
            return null;
        }

        return $value;
    }
}
