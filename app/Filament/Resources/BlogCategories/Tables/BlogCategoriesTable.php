<?php

namespace App\Filament\Resources\BlogCategories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlogCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->label('Название')->searchable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                TextColumn::make('sort_order')->label('Порядок')->sortable(),
                TextColumn::make('posts_count')->label('Статей')->counts('posts'),
                IconColumn::make('is_active')->label('Активна')->boolean(),
                TextColumn::make('updated_at')->label('Изменена')->dateTime()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
