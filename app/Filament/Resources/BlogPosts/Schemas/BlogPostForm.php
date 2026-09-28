<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Domain\Blog\BlogPostStatus;
use App\Domain\Users\Enums\PermissionName;
use App\Models\BlogCategory;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogPostForm
{
    private const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

    public static function configure(Schema $schema): Schema
    {
        $canPublish = fn (): bool => auth()->user()?->can(PermissionName::ContentPublish->value) ?? false;

        return $schema->components([
            Section::make('Основное')
                ->columns(2)
                ->schema([
                    TextInput::make('title')->label('Заголовок')->required()->maxLength(255),
                    TextInput::make('slug')->label('Slug')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255),
                    Select::make('tag_ids')
                        ->label('Теги / рубрики')
                        ->multiple()
                        ->options(fn () => BlogCategory::query()->orderBy('sort_order')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('reading_time')
                        ->label('Время чтения, минут')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(999),
                    Textarea::make('excerpt')->label('Краткое описание')->rows(4)->maxLength(2000)->columnSpanFull(),
                    FileUpload::make('cover_image')
                        ->label('Обложка')
                        ->disk('public')
                        ->directory('blog/covers')
                        ->acceptedFileTypes(self::IMAGE_TYPES)
                        ->maxSize(10240)
                        ->image(),
                ]),
            Section::make('Содержание статьи')
                ->description('Только фиксированный набор безопасных блоков. Произвольный HTML и скрипты не поддерживаются.')
                ->schema([
                    Builder::make('content')
                        ->label('Блоки')
                        ->required()
                        ->minItems(1)
                        ->blocks(self::articleBlocks())
                        ->addActionLabel('Добавить блок')
                        ->blockPickerColumns(2)
                        ->collapsible()
                        ->columnSpanFull(),
                ]),
            Section::make('Публикация')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Статус')
                        ->options(collect(BlogPostStatus::cases())->mapWithKeys(
                            fn (BlogPostStatus $status): array => [$status->value => $status->label()],
                        )->all())
                        ->default(BlogPostStatus::Draft->value)
                        ->required()
                        ->disabled(fn (): bool => ! $canPublish()),
                    DateTimePicker::make('published_at')
                        ->label('Дата публикации')
                        ->native(false)
                        ->disabled(fn (): bool => ! $canPublish()),
                ]),
            Section::make('SEO')
                ->columns(2)
                ->schema([
                    TextInput::make('seo_title')->label('SEO title')->maxLength(255),
                    Textarea::make('seo_description')->label('SEO description')->rows(3)->maxLength(1000),
                    FileUpload::make('og_image')
                        ->label('OpenGraph изображение')
                        ->disk('public')
                        ->directory('blog/og')
                        ->acceptedFileTypes(self::IMAGE_TYPES)
                        ->maxSize(10240)
                        ->image(),
                ]),
        ]);
    }

    /** @return list<Block> */
    private static function articleBlocks(): array
    {
        return [
            Block::make('heading')
                ->label('Заголовок')
                ->schema([
                    TextInput::make('text')->label('Текст')->required()->maxLength(500),
                    Select::make('level')->label('Уровень')->options([2 => 'H2', 3 => 'H3'])->default(2)->required(),
                ]),
            Block::make('rich_text')
                ->label('Текст')
                ->schema([
                    RichEditor::make('html')
                        ->label('Текст')
                        ->required()
                        ->toolbarButtons(['bold', 'italic', 'link', 'blockquote', 'undo', 'redo']),
                ]),
            Block::make('list')
                ->label('Список')
                ->schema([
                    Select::make('style')->label('Тип')->options([
                        'unordered' => 'Маркированный',
                        'ordered' => 'Нумерованный',
                    ])->default('unordered')->required(),
                    Repeater::make('items')
                        ->label('Пункты')
                        ->schema([
                            TextInput::make('text')->label('Текст')->required()->maxLength(1000),
                        ])
                        ->minItems(1)
                        ->maxItems(50)
                        ->required(),
                ]),
            Block::make('image')
                ->label('Изображение')
                ->schema(self::imageFields()),
            Block::make('wide_image')
                ->label('Широкое изображение')
                ->schema(self::imageFields()),
            Block::make('gallery')
                ->label('Галерея / две колонки')
                ->schema([
                    Repeater::make('images')
                        ->label('Изображения')
                        ->schema(self::imageFields())
                        ->minItems(2)
                        ->maxItems(4)
                        ->columns(2)
                        ->required(),
                ]),
        ];
    }

    /** @return array<int, FileUpload|TextInput> */
    private static function imageFields(): array
    {
        return [
            FileUpload::make('path')
                ->label('Файл')
                ->disk('public')
                ->directory('blog/content')
                ->acceptedFileTypes(self::IMAGE_TYPES)
                ->maxSize(10240)
                ->image()
                ->required(),
            TextInput::make('alt')->label('Alt')->required()->maxLength(500),
            TextInput::make('caption')->label('Подпись')->maxLength(1000),
        ];
    }
}
