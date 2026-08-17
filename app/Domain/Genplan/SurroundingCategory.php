<?php

namespace App\Domain\Genplan;

enum SurroundingCategory: string
{
    case Education = 'education';
    case Shopping = 'shopping';
    case Healthcare = 'healthcare';
    case Food = 'food';
    case Entertainment = 'entertainment';
    case Sport = 'sport';
    case Transport = 'transport';

    public function label(): string
    {
        return match ($this) {
            self::Education => 'Образование',
            self::Shopping => 'Магазины',
            self::Healthcare => 'Медицина',
            self::Food => 'Еда',
            self::Entertainment => 'Развлечения',
            self::Sport => 'Спорт',
            self::Transport => 'Транспорт',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::Education => 'О',
            self::Shopping => 'М',
            self::Healthcare => '+',
            self::Food => 'Е',
            self::Entertainment => 'Д',
            self::Sport => 'С',
            self::Transport => 'Т',
        };
    }

    public function order(): int
    {
        return array_search($this, self::cases(), true);
    }
}
