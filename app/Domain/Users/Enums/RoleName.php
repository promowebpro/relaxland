<?php

namespace App\Domain\Users\Enums;

enum RoleName: string
{
    case SuperAdmin = 'super-admin';
    case ContentManager = 'content-manager';
    case SalesManager = 'sales-manager';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Суперадминистратор',
            self::ContentManager => 'Контент-менеджер',
            self::SalesManager => 'Менеджер по продажам',
            self::Viewer => 'Наблюдатель',
        };
    }
}
