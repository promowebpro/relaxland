<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Domain\Users\Enums\RoleName;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Имя')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->revealable()
                    ->minLength(12)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('roles')
                    ->label('Роли')
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => RoleName::tryFrom($record->name)?->label() ?? $record->name)
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->disabled(self::isCurrentSuperAdmin(...)),
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true)
                    ->disabled(self::isCurrentSuperAdmin(...)),
            ]);
    }

    private static function isCurrentSuperAdmin(?User $record): bool
    {
        return $record !== null
            && auth()->user()?->is($record)
            && $record->hasRole(RoleName::SuperAdmin->value);
    }
}
