<?php

namespace App\Filament\Pages;

use App\Domain\Users\Enums\PermissionName;
use App\Models\AboutPage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AboutContentPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?string $navigationLabel = 'О нас';

    protected static ?string $title = 'Контент страницы «О нас»';

    protected static ?string $slug = 'about';

    protected static ?int $navigationSort = 11;

    protected string $view = 'filament.pages.home-content-page';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can(PermissionName::ContentView->value) ?? false;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->form->fill(AboutPage::pageContent());
    }

    public function form(Schema $schema): Schema
    {
        $readOnly = ! (auth()->user()?->can(PermissionName::ContentUpdate->value) ?? false);

        return $schema->statePath('data')->components([
            Section::make('Вступление и коллаж')->schema([
                TextInput::make('title')->label('Заголовок страницы')->required()->maxLength(255),
                TextInput::make('trust_title')->label('Заголовок вступления')->required()->maxLength(255),
                Textarea::make('trust_text')->label('Текст вступления')->rows(4)->required()->maxLength(4000),
                self::imageField('main_image', 'Большая фотография'),
                TextInput::make('main_image_alt')->label('Описание большой фотографии')->maxLength(500),
                self::imageField('left_image', 'Фотография слева'),
                TextInput::make('left_image_alt')->label('Описание фотографии слева')->maxLength(500),
                self::imageField('right_image', 'Фотография справа'),
                TextInput::make('right_image_alt')->label('Описание фотографии справа')->maxLength(500),
                TextInput::make('care_title')->label('Заголовок заботы')->required()->maxLength(255),
                Textarea::make('care_text')->label('Текст заботы')->rows(5)->required()->maxLength(5000),
            ])->disabled($readOnly),
            Section::make('Приглашение')->description('Общий блок для страниц «О нас» и «Контакты».')->schema([
                Textarea::make('visit_title')->label('Заголовок — переносы строк сохраняются')->rows(3)->required()->maxLength(255),
                Textarea::make('visit_text')->label('Описание')->rows(4)->required()->maxLength(2000),
                Repeater::make('visit_tags')->label('Плашки приглашения')->schema([
                    TextInput::make('label')->label('Текст')->required()->maxLength(60),
                ])->maxItems(8),
                TextInput::make('visit_button')->label('Текст кнопки записи')->required()->maxLength(100),
            ])->disabled($readOnly),
            Section::make('Карта и маршрут')->description('Живая карта OpenStreetMap. Координаты задают положение метки; без собственных ссылок маршруты строятся к этой точке.')->columns(2)->schema([
                TextInput::make('map_latitude')->label('Широта')->numeric()->required()->minValue(-85)->maxValue(85),
                TextInput::make('map_longitude')->label('Долгота')->numeric()->required()->minValue(-180)->maxValue(180),
                TextInput::make('map_zoom')->label('Начальный масштаб')->integer()->required()->minValue(3)->maxValue(18),
                TextInput::make('map_label')->label('Подпись метки')->required()->maxLength(255),
                TextInput::make('route_title')->label('Заголовок маршрута')->required()->maxLength(100),
                TextInput::make('travel_title')->label('Подзаголовок поездки')->required()->maxLength(100),
                Textarea::make('travel_text')->label('Описание поездки')->maxLength(500),
                TextInput::make('coordinates_title')->label('Подпись координат')->required()->maxLength(100),
                TextInput::make('route_yandex')->label('Своя ссылка Яндекс')->url()->maxLength(2000),
                TextInput::make('route_google')->label('Своя ссылка Google')->url()->maxLength(2000),
                TextInput::make('route_two_gis')->label('Своя ссылка 2ГИС')->url()->maxLength(2000),
                self::imageField('map_mascot', 'Персонаж поверх карты (пусто — ёжик из макета)'),
            ])->disabled($readOnly),
            Section::make('Опыт и показатели')->schema([
                Textarea::make('numbers_title')->label('Заголовок блока')->rows(2)->required()->maxLength(255),
                Repeater::make('cards')->label('Карточки — порядок соответствует макету')->schema([
                    TextInput::make('value')->label('Значение')->required()->maxLength(20),
                    Textarea::make('label')->label('Подпись')->rows(2)->required()->maxLength(100),
                    Textarea::make('text')->label('Описание')->rows(4)->required()->maxLength(1500),
                ])->minItems(1)->maxItems(8),
                self::imageField('cards_mascot', 'Персонаж поверх карточки (пусто — собака из макета)'),
            ])->disabled($readOnly),
            Section::make('SEO')->schema([
                TextInput::make('seo_title')->label('SEO title')->maxLength(255),
                Textarea::make('seo_description')->label('SEO description')->maxLength(1000),
            ])->disabled($readOnly),
        ]);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can(PermissionName::ContentUpdate->value), 403);
        $data = $this->form->getState();
        $record = AboutPage::query()->first() ?? new AboutPage;
        $record->fill(['content' => $data])->save();
        Notification::make()->title('Страница «О нас» сохранена')->success()->send();
    }

    private static function imageField(string $name, string $label): FileUpload
    {
        return FileUpload::make($name)->label($label)->disk('public')->directory('about')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
            ->maxSize(15360)->image();
    }
}
