<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Domain\Leads\LeadFormType;
use App\Domain\Leads\LeadSource;
use App\Domain\Leads\LeadStatus;
use App\Domain\Settings\SiteSettings;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Контакт и обращение')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')->label('Имя')->placeholder('Не указано'),
                    TextEntry::make('phone')
                        ->label('Телефон')
                        ->copyable()
                        ->url(fn (string $state): string => SiteSettings::phoneHref($state) ?? '#'),
                    TextEntry::make('email')->label('Email')->copyable()->placeholder('Не указан')->url(fn (?string $state): ?string => $state ? 'mailto:'.$state : null),
                    TextEntry::make('created_at')->label('Создана')->dateTime(),
                    TextEntry::make('message')->label('Сообщение')->placeholder('Не указано')->columnSpanFull(),
                    TextEntry::make('source')->label('Источник')->formatStateUsing(fn (LeadSource $state): string => $state->label()),
                    TextEntry::make('form_type')->label('Тип формы')->formatStateUsing(fn (LeadFormType $state): string => $state->label()),
                    TextEntry::make('page_url')->label('Страница')->url(fn (?string $state): ?string => $state)->openUrlInNewTab()->placeholder('Не указана')->columnSpanFull(),
                ]),
            Section::make('Работа с заявкой')
                ->columns(2)
                ->schema([
                    TextEntry::make('status')->label('Статус')->badge()->formatStateUsing(fn (LeadStatus $state): string => $state->label()),
                    TextEntry::make('assignedUser.name')->label('Ответственный')->placeholder('Не назначен'),
                    TextEntry::make('manager_comment')->label('Комментарий менеджера')->placeholder('Не указан')->columnSpanFull(),
                ]),
            Section::make('Атрибуция и согласие')
                ->columns(2)
                ->schema([
                    TextEntry::make('utm_source')->label('UTM source')->placeholder('—'),
                    TextEntry::make('utm_medium')->label('UTM medium')->placeholder('—'),
                    TextEntry::make('utm_campaign')->label('UTM campaign')->placeholder('—'),
                    TextEntry::make('utm_content')->label('UTM content')->placeholder('—'),
                    TextEntry::make('utm_term')->label('UTM term')->placeholder('—'),
                    TextEntry::make('consent_given_at')->label('Согласие дано')->dateTime()->placeholder('Не зафиксировано'),
                    TextEntry::make('privacyDocument.title')->label('Документ согласия')->placeholder('Не зафиксирован'),
                    TextEntry::make('privacy_document_version')->label('Версия документа')->placeholder('Не указана'),
                ]),
        ]);
    }
}
