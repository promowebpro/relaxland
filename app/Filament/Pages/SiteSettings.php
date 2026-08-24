<?php

namespace App\Filament\Pages;

use App\Domain\Settings\SettingsRepository;
use App\Domain\Users\Enums\PermissionName;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Сайт';

    protected static ?string $navigationLabel = 'Настройки сайта';

    protected static ?string $title = 'Глобальные настройки';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.site-settings';

    /**
     * @var array<string, string>
     */
    public const FIELD_MAP = [
        'primary_phone' => 'contacts.phone',
        'sales_phone' => 'contacts.sales_phone',
        'email' => 'contacts.email',
        'support_email' => 'contacts.support_email',
        'working_hours' => 'contacts.working_hours',
        'office_address' => 'contacts.office_address',
        'village_address' => 'contacts.village_address',
        'village_latitude' => 'contacts.village_latitude',
        'village_longitude' => 'contacts.village_longitude',
        'notice' => 'contacts.notice',
        'travel_time' => 'contacts.travel_time',
        'telegram' => 'social.telegram',
        'vk' => 'social.vk',
        'whatsapp' => 'social.whatsapp',
        'max' => 'social.max',
        'route_yandex' => 'routes.yandex',
        'route_google' => 'routes.google',
        'route_two_gis' => 'routes.two_gis',
        'presentation_url' => 'documents.presentation_url',
        'presentation_file' => 'documents.presentation_file',
        'copyright' => 'footer.copyright',
        'footer_disclaimer' => 'footer.disclaimer',
        'seo_site_title' => 'seo.site_title',
        'seo_title_suffix' => 'seo.title_suffix',
        'seo_default_description' => 'seo.default_description',
        'seo_default_og_image' => 'seo.default_og_image',
        'seo_organization_name' => 'seo.organization_name',
        'seo_default_locale' => 'seo.default_locale',
    ];

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can(PermissionName::SettingsView->value) ?? false;
    }

    public function mount(SettingsRepository $settings): void
    {
        abort_unless(static::canAccess(), 403);

        $data = [];

        foreach (self::FIELD_MAP as $field => $key) {
            $data[$field] = $settings->get($key);
        }

        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        $readOnly = fn (): bool => ! (auth()->user()?->can(PermissionName::SettingsManage->value) ?? false);

        return $schema
            ->statePath('data')
            ->components([
                Section::make('Контакты')
                    ->description('Данные используются в header, footer и на странице контактов.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('primary_phone')->label('Основной телефон')->tel()->maxLength(100)->disabled($readOnly),
                        TextInput::make('sales_phone')->label('Телефон отдела продаж')->tel()->maxLength(100)->disabled($readOnly),
                        TextInput::make('email')->label('Общий email')->email()->maxLength(255)->disabled($readOnly),
                        TextInput::make('support_email')->label('Email поддержки')->email()->maxLength(255)->disabled($readOnly),
                        TextInput::make('working_hours')->label('Режим работы')->maxLength(255)->disabled($readOnly),
                        TextInput::make('travel_time')->label('Информация о времени поездки')->maxLength(255)->disabled($readOnly),
                        Textarea::make('office_address')->label('Адрес офиса')->rows(3)->disabled($readOnly),
                        Textarea::make('village_address')->label('Адрес посёлка')->rows(3)->disabled($readOnly),
                        TextInput::make('village_latitude')->label('Широта посёлка')->numeric()->step(0.0000001)->minValue(-90)->maxValue(90)->disabled($readOnly),
                        TextInput::make('village_longitude')->label('Долгота посёлка')->numeric()->step(0.0000001)->minValue(-180)->maxValue(180)->disabled($readOnly),
                        Textarea::make('notice')->label('Предупреждение на странице контактов')->rows(3)->columnSpanFull()->disabled($readOnly),
                    ]),
                Section::make('Социальные сети')
                    ->columns(2)
                    ->schema([
                        TextInput::make('telegram')->label('Telegram')->url()->maxLength(2048)->disabled($readOnly),
                        TextInput::make('vk')->label('VK')->url()->maxLength(2048)->disabled($readOnly),
                        TextInput::make('whatsapp')->label('WhatsApp')->url()->maxLength(2048)->disabled($readOnly),
                        TextInput::make('max')->label('MAX')->url()->maxLength(2048)->disabled($readOnly),
                    ]),
                Section::make('Маршруты')
                    ->columns(3)
                    ->schema([
                        TextInput::make('route_yandex')->label('Яндекс Карты')->url()->maxLength(2048)->disabled($readOnly),
                        TextInput::make('route_google')->label('Google Maps')->url()->maxLength(2048)->disabled($readOnly),
                        TextInput::make('route_two_gis')->label('2GIS')->url()->maxLength(2048)->disabled($readOnly),
                    ]),
                Section::make('Документы и footer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('presentation_url')->label('Ссылка на презентацию')->url()->maxLength(2048)->disabled($readOnly),
                        FileUpload::make('presentation_file')
                            ->label('Файл презентации')
                            ->disk('public')
                            ->directory('presentations')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20480)
                            ->disabled($readOnly),
                        TextInput::make('copyright')->label('Copyright')->maxLength(255)->disabled($readOnly),
                        Textarea::make('footer_disclaimer')->label('Служебный дисклеймер')->rows(4)->columnSpanFull()->disabled($readOnly),
                    ]),
                Section::make('SEO')
                    ->description('Глобальные fallback-значения. Индексация окружения включается только через production-конфигурацию, а не из админки.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('seo_site_title')
                            ->label('Название сайта')
                            ->maxLength(70)
                            ->helperText('Используется как глобальный fallback и og:site_name.')
                            ->disabled($readOnly),
                        TextInput::make('seo_title_suffix')
                            ->label('Суффикс title')
                            ->maxLength(50)
                            ->helperText('Необязательный текст после заголовка страницы; дубликаты автоматически исключаются.')
                            ->disabled($readOnly),
                        Textarea::make('seo_default_description')
                            ->label('Описание по умолчанию')
                            ->rows(3)
                            ->maxLength(320)
                            ->columnSpanFull()
                            ->disabled($readOnly),
                        TextInput::make('seo_organization_name')
                            ->label('Юридическое / публичное название организации')
                            ->maxLength(150)
                            ->helperText('Попадает в Organization JSON-LD. Заполняйте только фактическим названием.')
                            ->disabled($readOnly),
                        Select::make('seo_default_locale')
                            ->label('Локаль metadata')
                            ->options(['ru_RU' => 'Русский (ru_RU)', 'en_RU' => 'English (en_RU)'])
                            ->default('ru_RU')
                            ->disabled($readOnly),
                        FileUpload::make('seo_default_og_image')
                            ->label('OpenGraph изображение по умолчанию')
                            ->disk('public')
                            ->directory('seo')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->helperText('JPEG, PNG или WebP до 5 МБ. Тег не выводится, если файл недоступен.')
                            ->columnSpanFull()
                            ->disabled($readOnly),
                    ]),
            ]);
    }

    public function save(SettingsRepository $settings): void
    {
        abort_unless(auth()->user()?->can(PermissionName::SettingsManage->value), 403);

        $data = $this->form->getState();

        foreach (self::FIELD_MAP as $field => $key) {
            $value = $data[$field] ?? null;

            if ($value === null || $value === '') {
                $settings->forget($key);

                continue;
            }

            $settings->set($key, $value, str($key)->before('.')->toString(), true);
        }

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}
