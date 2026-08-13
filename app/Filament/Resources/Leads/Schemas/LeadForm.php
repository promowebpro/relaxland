<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Domain\Leads\AssignableLeadManagers;
use App\Domain\Leads\LeadFormType;
use App\Domain\Leads\LeadSource;
use App\Domain\Leads\LeadStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Обращение')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->label('Имя')->disabled()->dehydrated(false),
                    TextInput::make('phone')->label('Телефон')->disabled()->dehydrated(false)->copyable(),
                    TextInput::make('email')->label('Email')->disabled()->dehydrated(false)->copyable(),
                    TextInput::make('created_at')->label('Создана')->disabled()->dehydrated(false),
                    Textarea::make('message')->label('Сообщение')->disabled()->dehydrated(false)->rows(5)->columnSpanFull(),
                    TextInput::make('source')
                        ->label('Источник')
                        ->formatStateUsing(fn (LeadSource|string|null $state): ?string => $state instanceof LeadSource ? $state->label() : $state)
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('form_type')
                        ->label('Тип формы')
                        ->formatStateUsing(fn (LeadFormType|string|null $state): ?string => $state instanceof LeadFormType ? $state->label() : $state)
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('page_url')->label('Страница')->disabled()->dehydrated(false)->columnSpanFull(),
                ]),
            Section::make('Работа с заявкой')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Статус')
                        ->options(collect(LeadStatus::cases())->mapWithKeys(
                            fn (LeadStatus $status): array => [$status->value => $status->label()],
                        )->all())
                        ->required(),
                    Select::make('assigned_to')
                        ->label('Ответственный')
                        ->options(fn (): array => app(AssignableLeadManagers::class)->options())
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Textarea::make('manager_comment')
                        ->label('Комментарий менеджера')
                        ->rows(6)
                        ->maxLength(5000)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
