<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Domain\Users\Enums\RoleName;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Роль')
                    ->formatStateUsing(fn (string $state): string => RoleName::tryFrom($state)?->label() ?? $state)
                    ->searchable(),
                TextColumn::make('permissions_count')
                    ->label('Разрешений')
                    ->counts('permissions'),
                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Изменена')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
