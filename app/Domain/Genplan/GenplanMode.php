<?php

namespace App\Domain\Genplan;

enum GenplanMode: string
{
    case TwoD = '2d';
    case ThreeD = '3d';

    public function label(): string
    {
        return strtoupper($this->value);
    }

    public static function default(): self
    {
        return self::ThreeD;
    }
}
