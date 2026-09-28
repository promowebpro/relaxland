<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Domain\Blog\BlogPostStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('Заголовок')->searchable()->limit(60),
                TextColumn::make('tags.name')->label('Теги / рубрики')->badge(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (BlogPostStatus $state): string => $state->label()),
                TextColumn::make('published_at')->label('Публикация')->dateTime()->sortable(),
                TextColumn::make('updated_at')->label('Изменена')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('tags')->label('Теги / рубрики')->relationship('tags', 'name')->multiple(),
                SelectFilter::make('status')->label('Статус')->options(
                    collect(BlogPostStatus::cases())->mapWithKeys(
                        fn (BlogPostStatus $status): array => [$status->value => $status->label()],
                    )->all(),
                ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
