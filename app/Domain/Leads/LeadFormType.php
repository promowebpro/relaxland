<?php

namespace App\Domain\Leads;

enum LeadFormType: string
{
    case Callback = 'callback';
    case Visit = 'visit';
    case Consultation = 'consultation';
    case Generic = 'generic';

    public function label(): string
    {
        return match ($this) {
            self::Callback => 'Обратный звонок',
            self::Visit => 'Запись на визит',
            self::Consultation => 'Консультация',
            self::Generic => 'Обращение',
        };
    }
}
