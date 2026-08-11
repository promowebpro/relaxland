<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Domain\Users\Enums\RoleName;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Системное имя')
                    ->helperText('Используйте латиницу, цифры и дефисы.')
                    ->regex('/^[a-z0-9-]+$/')
                    ->notIn([RoleName::SuperAdmin->value])
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->required(),
                Hidden::make('guard_name')
                    ->default('web'),
                CheckboxList::make('permissions')
                    ->label('Разрешения')
                    ->relationship('permissions', 'name')
                    ->columns(2)
                    ->bulkToggleable()
                    ->searchable(),
            ]);
    }
}
