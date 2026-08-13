<?php

namespace App\Domain\Leads;

enum LeadSource: string
{
    case Home = 'home';
    case Contacts = 'contacts';
    case About = 'about';
    case Header = 'header';
    case Footer = 'footer';
    case GenplanPreview = 'genplan-preview';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Главная',
            self::Contacts => 'Контакты',
            self::About => 'О нас',
            self::Header => 'Шапка сайта',
            self::Footer => 'Подвал сайта',
            self::GenplanPreview => 'Превью генплана',
        };
    }
}
