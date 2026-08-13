<?php

namespace App\Domain\Genplan;

enum PlotStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Доступен',
            self::Reserved => 'Забронирован',
            self::Sold => 'Продан',
            self::Hidden => 'Скрыт',
        };
    }
}
