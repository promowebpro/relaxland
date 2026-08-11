<?php

namespace App\Filament\Resources\LegalDocuments\Schemas;

use App\Domain\Content\LegalDocumentType;
use App\Domain\Users\Enums\PermissionName;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LegalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Документ')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Тип')
                            ->options(collect(LegalDocumentType::cases())->mapWithKeys(
                                fn (LegalDocumentType $type): array => [$type->value => $type->label()],
                            )->all())
                            ->required(),
                        TextInput::make('version')
                            ->label('Версия')
                            ->maxLength(255),
                        RichEditor::make('content')
                            ->label('Содержание')
                            ->helperText('Разрешены только безопасные базовые элементы форматирования.')
                            ->required(fn (Get $get): bool => blank($get('file_path')))
                            ->columnSpanFull(),
                        FileUpload::make('file_path')
                            ->label('PDF-файл')
                            ->disk('public')
                            ->directory('legal-documents')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->required(fn (Get $get): bool => blank($get('content')))
                            ->columnSpanFull(),
                    ]),
                Section::make('Публикация')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('published_at')
                            ->label('Дата публикации')
                            ->native(false)
                            ->disabled(fn (): bool => ! (auth()->user()?->can(PermissionName::ContentPublish->value) ?? false)),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(false)
                            ->disabled(fn (): bool => ! (auth()->user()?->can(PermissionName::ContentPublish->value) ?? false)),
                    ]),
            ]);
    }
}
