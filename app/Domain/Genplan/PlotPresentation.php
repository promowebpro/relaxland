<?php

namespace App\Domain\Genplan;

final class PlotPresentation
{
    public static function area(string $value): string
    {
        return self::decimal($value, 2).' сот.';
    }

    public static function money(?string $value): string
    {
        if ($value === null || preg_match('/^0+(?:\.0+)?$/', $value)) {
            return 'Цена по запросу';
        }

        return self::decimal($value, 2).' ₽';
    }

    public static function moneyPerSotka(?string $value): string
    {
        if ($value === null || preg_match('/^0+(?:\.0+)?$/', $value)) {
            return 'Не указана';
        }

        return self::decimal($value, 2).' ₽/сот.';
    }

    private static function decimal(string $value, int $scale): string
    {
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $whole = ltrim($whole, '0') ?: '0';
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', ' ', $whole) ?? $whole;
        $fraction = rtrim(substr(str_pad($fraction, $scale, '0'), 0, $scale), '0');

        return $whole.($fraction !== '' ? ','.$fraction : '');
    }
}
