<?php

namespace App\Filament\Resources\Stories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')->label('Имя')->searchable()->limit(40),
                TextColumn::make('subtitle')->label('Подпись')->toggleable()->limit(40),
                IconColumn::make('audio')->label('Аудио')->boolean()->getStateUsing(fn ($record) => filled($record->audio)),
                IconColumn::make('video')->label('Видео')->boolean()->getStateUsing(fn ($record) => filled($record->video)),
                TextColumn::make('sort_order')->label('Порядок')->sortable(),
                IconColumn::make('is_active')->label('Опубликована')->boolean(),
                TextColumn::make('updated_at')->label('Изменена')->dateTime()->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([]);
    }
}
