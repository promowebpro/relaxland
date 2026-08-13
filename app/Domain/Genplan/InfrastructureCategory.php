<?php

namespace App\Domain\Genplan;

enum InfrastructureCategory: string
{
    case Playground = 'playground';
    case Sport = 'sport';
    case Service = 'service';
    case Transport = 'transport';
    case Nature = 'nature';

    public function label(): string
    {
        return match ($this) {
            self::Playground => 'Детские площадки',
            self::Sport => 'Спорт',
            self::Service => 'Сервис',
            self::Transport => 'Транспорт',
            self::Nature => 'Природа и отдых',
        };
    }
}
