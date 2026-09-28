<?php

namespace App\Filament\Pages;

use App\Domain\Users\Enums\PermissionName;
use App\Models\HomePage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class HomeContentPage extends Page
{
    private const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?string $navigationLabel = 'Главная';

    protected static ?string $title = 'Контент главной страницы';

    protected static ?string $slug = 'home';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.home-content-page';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public ?int $recordId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can(PermissionName::ContentView->value) ?? false;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $record = HomePage::query()->first();
        $this->recordId = $record?->getKey();
        $this->form->fill($record?->toArray() ?? HomePage::defaultContent());
    }

    public function form(Schema $schema): Schema
    {
        $readOnly = fn (): bool => ! (auth()->user()?->can(PermissionName::ContentUpdate->value) ?? false);
        $cannotPublish = fn (): bool => ! (auth()->user()?->can(PermissionName::ContentPublish->value) ?? false);

        return $schema
            ->statePath('data')
            ->components([
                Section::make('Hero')
                    ->description('Первый экран: ключевое обещание, стоимость и responsive media.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('hero_eyebrow')->label('Надзаголовок')->maxLength(255)->disabled($readOnly),
                        TextInput::make('hero_price')->label('Строка о стоимости')->maxLength(255)->disabled($readOnly),
                        TextInput::make('hero_title')->label('Заголовок')->required()->maxLength(255)->columnSpanFull()->disabled($readOnly),
                        Textarea::make('hero_description')->label('Описание')->rows(4)->maxLength(2000)->columnSpanFull()->disabled($readOnly),
                        ...self::responsiveImageFields('hero', 'home/hero', $readOnly),
                    ]),
                Section::make('Преимущества и атмосфера')
                    ->columns(2)
                    ->schema([
                        TextInput::make('intro_title')->label('Заголовок вступления')->maxLength(255)->disabled($readOnly),
                        Textarea::make('intro_text')->label('Текст вступления')->rows(4)->disabled($readOnly),
                        Repeater::make('benefits')->label('Преимущества')->schema(self::textItemFields($readOnly))->minItems(1)->maxItems(8)->columnSpanFull()->disabled($readOnly),
                        TextInput::make('atmosphere_title')->label('Заголовок атмосферы')->maxLength(255)->disabled($readOnly),
                        Textarea::make('atmosphere_text')->label('Текст атмосферы')->rows(4)->disabled($readOnly),
                        FileUpload::make('atmosphere_image')->label('Изображение атмосферы')->disk('public')->directory('home/atmosphere')->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(15360)->image()->disabled($readOnly),
                        TextInput::make('atmosphere_image_alt')->label('Alt изображения')->maxLength(500)->disabled($readOnly),
                    ]),
                Section::make('Сценарии жизни')
                    ->schema([
                        Repeater::make('life_scenarios')->label('Карточки сценариев')->schema(self::mediaItemFields($readOnly))->minItems(1)->maxItems(8)->columns(2)->disabled($readOnly),
                    ]),
                Section::make('Забота и сервис')
                    ->columns(2)
                    ->schema([
                        TextInput::make('care_title')->label('Заголовок')->maxLength(255)->disabled($readOnly),
                        Textarea::make('care_text')->label('Описание')->rows(3)->disabled($readOnly),
                        Repeater::make('care_items')->label('Слайды заботы и сервиса')->schema(self::careItemFields($readOnly))->minItems(1)->maxItems(5)->columns(2)->columnSpanFull()->disabled($readOnly),
                    ]),
                Section::make('Сезоны')
                    ->columns(2)
                    ->schema([
                        TextInput::make('seasons_title')->label('Заголовок')->maxLength(255)->disabled($readOnly),
                        Textarea::make('seasons_text')->label('Описание')->rows(3)->disabled($readOnly),
                        Repeater::make('seasons')->label('Сезоны / состояния переключателя')->schema(self::mediaItemFields($readOnly))->minItems(1)->maxItems(8)->columns(2)->columnSpanFull()->disabled($readOnly),
                    ]),
                Section::make('CTA и схема территории')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cta_title')->label('CTA заголовок')->maxLength(255)->disabled($readOnly),
                        Textarea::make('cta_text')->label('CTA текст')->rows(3)->disabled($readOnly),
                        TextInput::make('genplan_title')->label('Заголовок схемы')->maxLength(255)->disabled($readOnly),
                        Textarea::make('genplan_text')->label('Описание схемы')->rows(3)->disabled($readOnly),
                        FileUpload::make('genplan_image')->label('Статичная схема')->disk('public')->directory('home/genplan')->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(20480)->image()->disabled($readOnly),
                        TextInput::make('genplan_image_alt')->label('Alt схемы')->maxLength(500)->disabled($readOnly),
                    ]),
                Section::make('Варианты покупки')
                    ->columns(2)
                    ->schema([
                        TextInput::make('purchase_title')->label('Заголовок')->maxLength(255)->disabled($readOnly),
                        Textarea::make('purchase_text')->label('Описание')->rows(3)->disabled($readOnly),
                        Repeater::make('purchase_options')->label('Варианты')->schema(self::textItemFields($readOnly))->minItems(1)->maxItems(8)->columnSpanFull()->disabled($readOnly),
                    ]),
                Section::make('Истории, визит и застройщик')
                    ->columns(2)
                    ->schema([
                        TextInput::make('stories_title')->label('Заголовок историй')->maxLength(255)->disabled($readOnly),
                        Textarea::make('stories_text')->label('Описание историй')->rows(3)->disabled($readOnly),
                        TextInput::make('visit_title')->label('Заголовок визита')->maxLength(255)->disabled($readOnly),
                        Textarea::make('visit_text')->label('Описание визита')->rows(3)->disabled($readOnly),
                        TextInput::make('developer_title')->label('Заголовок о проекте')->maxLength(255)->disabled($readOnly),
                        Textarea::make('developer_text')->label('Текст о проекте')->rows(4)->disabled($readOnly),
                        FileUpload::make('developer_image')->label('Изображение')->disk('public')->directory('home/developer')->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(15360)->image()->disabled($readOnly),
                        TextInput::make('developer_image_alt')->label('Alt изображения')->maxLength(500)->disabled($readOnly),
                    ]),
                Section::make('Блог')
                    ->columns(2)
                    ->schema([
                        TextInput::make('blog_title')->label('Заголовок')->maxLength(255)->disabled($readOnly),
                        Textarea::make('blog_text')->label('Описание')->rows(3)->disabled($readOnly),
                    ]),
                Section::make('SEO и публикация')
                    ->columns(2)
                    ->schema([
                        TextInput::make('seo_title')->label('SEO title')->maxLength(255)->disabled($readOnly),
                        Textarea::make('seo_description')->label('SEO description')->rows(3)->maxLength(1000)->disabled($readOnly),
                        FileUpload::make('og_image')->label('OpenGraph изображение')->disk('public')->directory('home/og')->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(15360)->image()->disabled($readOnly),
                        Toggle::make('is_active')->label('Опубликована')->disabled(fn (): bool => $readOnly() || $cannotPublish()),
                    ]),
            ]);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can(PermissionName::ContentUpdate->value), 403);

        $data = $this->form->getState();
        $record = $this->recordId ? HomePage::query()->find($this->recordId) : null;

        if (! auth()->user()?->can(PermissionName::ContentPublish->value)) {
            $data['is_active'] = $record?->is_active ?? false;
        }

        $record ??= new HomePage;
        $record->fill($data)->save();
        $this->recordId = $record->getKey();

        Notification::make()->title('Главная страница сохранена')->success()->send();
    }

    /** @return array<int, TextInput|Textarea> */
    private static function textItemFields(callable $readOnly): array
    {
        return [
            TextInput::make('title')->label('Заголовок')->required()->maxLength(255)->disabled($readOnly),
            Textarea::make('text')->label('Текст')->rows(3)->maxLength(2000)->disabled($readOnly),
        ];
    }

    /** @return array<int, TextInput|Textarea|FileUpload> */
    private static function mediaItemFields(callable $readOnly): array
    {
        return [
            TextInput::make('label')->label('Короткая метка')->maxLength(100)->disabled($readOnly),
            TextInput::make('title')->label('Заголовок')->required()->maxLength(255)->disabled($readOnly),
            Textarea::make('text')->label('Текст')->rows(3)->maxLength(2000)->disabled($readOnly),
            FileUpload::make('image')->label('Desktop изображение')->disk('public')->directory('home/collections')->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(15360)->image()->disabled($readOnly),
            FileUpload::make('image_mobile')->label('Mobile изображение')->disk('public')->directory('home/collections')->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(15360)->image()->disabled($readOnly),
            TextInput::make('image_alt')->label('Alt')->maxLength(500)->disabled($readOnly),
        ];
    }

    /** @return array<int, FileUpload|Select|TextInput|Textarea> */
    private static function careItemFields(callable $readOnly): array
    {
        return [
            Textarea::make('title')->label('Заголовок')->required()->rows(2)->maxLength(255)->disabled($readOnly),
            Select::make('icon')->label('Иконка')->options([
                'heart' => 'Сердце — служба заботы',
                'utilities' => 'Лампочка — коммуникации',
                'home' => 'Дом — семья',
                'sport' => 'Мяч — спорт',
                'security' => 'Щит — безопасность',
            ])->required()->disabled($readOnly),
            Textarea::make('text')->label('Пункты списка — каждый с новой строки')->rows(9)->maxLength(2000)->columnSpanFull()->disabled($readOnly),
            Textarea::make('note')->label('Дополнительное примечание')->rows(4)->maxLength(2000)->columnSpanFull()->disabled($readOnly),
            Select::make('image_preset')->label('Исходное изображение')->options([
                'service' => 'Белый кролик — служба заботы',
                'utilities' => 'Газ — коммуникации',
                'family' => 'Птица — семья',
                'sport' => 'Теннисный мяч — спорт',
                'security' => 'Камера — безопасность',
            ])->disabled($readOnly),
            FileUpload::make('image')->label('Свое desktop изображение')->disk('public')->directory('home/care')->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(20480)->image()->disabled($readOnly),
            FileUpload::make('image_mobile')->label('Свое mobile изображение')->disk('public')->directory('home/care')->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(20480)->image()->disabled($readOnly),
            TextInput::make('image_alt')->label('Alt изображения')->maxLength(500)->columnSpanFull()->disabled($readOnly),
        ];
    }

    /** @return array<int, FileUpload|TextInput> */
    private static function responsiveImageFields(string $prefix, string $directory, callable $readOnly): array
    {
        return [
            FileUpload::make("{$prefix}_image")->label('Desktop изображение')->disk('public')->directory($directory)->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(20480)->image()->disabled($readOnly),
            FileUpload::make("{$prefix}_image_mobile")->label('Mobile изображение')->disk('public')->directory($directory)->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(20480)->image()->disabled($readOnly),
            TextInput::make("{$prefix}_image_alt")->label('Alt изображения')->maxLength(500)->columnSpanFull()->disabled($readOnly),
        ];
    }
}
