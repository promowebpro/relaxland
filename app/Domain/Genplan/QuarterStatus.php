<?php

namespace App\Domain\Genplan;

enum QuarterStatus: string
{
    case Available = 'available';
    case Limited = 'limited';
    case SoldOut = 'sold_out';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Доступен',
            self::Limited => 'Ограниченное предложение',
            self::SoldOut => 'Продан',
            self::Hidden => 'Скрыт',
        };
    }
}
