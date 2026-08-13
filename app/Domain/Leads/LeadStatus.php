<?php

namespace App\Domain\Leads;

enum LeadStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Contacted = 'contacted';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новая',
            self::InProgress => 'В работе',
            self::Contacted => 'Связались',
            self::Completed => 'Завершена',
            self::Rejected => 'Отклонена',
            self::Spam => 'Спам',
        };
    }
}
