<?php

namespace App\Filament\Resources\BlogCategories\Schemas;

use App\Domain\Users\Enums\PermissionName;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Категория')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('sort_order')
                        ->label('Порядок')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Активна')
                        ->default(false)
                        ->disabled(fn (): bool => ! (auth()->user()?->can(PermissionName::ContentPublish->value) ?? false)),
                ]),
        ]);
    }
}
