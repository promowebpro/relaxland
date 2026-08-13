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
}
