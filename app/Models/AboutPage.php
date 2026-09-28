<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AboutPage extends Model
{
    protected $fillable = ['content'];

    protected function casts(): array
    {
        return ['content' => 'array'];
    }

    public static function pageContent(): array
    {
        return array_replace(self::defaults(), self::query()->first()?->content ?? []);
    }

    /**
     * Shared visit/map block content for about, contacts, and home.
     * Optional site settings override coordinates, travel text, and route links.
     */
    public static function visitContent(array $settings = []): array
    {
        $content = self::pageContent();

        foreach ([
            'map_latitude' => 'contacts.village_latitude',
            'map_longitude' => 'contacts.village_longitude',
            'travel_text' => 'contacts.travel_time',
            'route_yandex' => 'routes.yandex',
            'route_google' => 'routes.google',
            'route_two_gis' => 'routes.two_gis',
        ] as $field => $setting) {
            if (filled($settings[$setting] ?? null)) {
                $content[$field] = $settings[$setting];
            }
        }

        return $content;
    }

    public static function imageUrl(?string $path): string
    {
        if (! $path || str_contains($path, '..')) {
            return '';
        }

        return str_starts_with($path, 'assets/') ? asset($path) : Storage::disk('public')->url($path);
    }

    public static function defaults(): array
    {
        return [
            'title' => 'О девелопере',
            'trust_title' => 'Нам доверяют',
            'trust_text' => 'Нам доверяют, потому что мы находимся на рынке более 20 лет и научились заботиться о своих клиентах. Оформив сделку, остаёмся в контакте и помогаем освоиться на новом месте. С нами вы не только находите участок для дома мечты, но и дружелюбную поддержку, которая не имеет аналогов.',
            'main_image' => null,
            'main_image_alt' => 'Летний ужин с друзьями на природе',
            'left_image' => null,
            'left_image_alt' => 'Отдых в тени леса',
            'right_image' => null,
            'right_image_alt' => 'Стрекоза среди трав',
            'care_title' => 'Забота о клиенте — наш приоритет',
            'care_text' => 'У нас годами проверенный опыт делать посёлки, в которых людям живётся ХА-РА-ШО. Городскому жителю бывает непросто адаптироваться к выходным за МКАДом, поэтому служба заботы станет для вас надёжной опорой и поддержит в любой бытовой ситуации. Встретить доставку, срочно прислать сантехника, постричь газон и т. п. Просто скажите, как вам помочь, и всё будет сделано на пять звёзд.',
            'visit_title' => "Приглашаем\nпротестировать\nлучшую жизнь",
            'visit_text' => 'Приезжайте с детьми, друзьями, домашними питомцами и отдохните как следует. Познакомитесь с будущими соседями, осмотритесь и влюбитесь в это место.',
            'visit_tags' => [['label' => 'Барбекю'], ['label' => 'Свежий воздух'], ['label' => 'Лес'], ['label' => 'Новые знакомства']],
            'visit_button' => 'Записаться',
            'route_title' => 'Построить маршрут',
            'travel_title' => 'На автомобиле',
            'travel_text' => "80 минут от МКАД\nпо скоростному шоссе",
            'coordinates_title' => 'Координаты посёлка',
            'map_latitude' => 55.670467,
            'map_longitude' => 35.664318,
            'map_zoom' => 12,
            'map_label' => 'Релакс Лэнд Можайский',
            'map_mascot' => null,
            'cards_mascot' => null,
            'route_yandex' => null,
            'route_google' => null,
            'route_two_gis' => null,
            'numbers_title' => "У нас годами проверенный\nопыт делать посёлки",
            'cards' => [
                ['value' => '25', 'label' => "лет\nв недвижимости", 'text' => 'Наша многолетняя экспертиза позволила сразу определить ключевые детали: удобное расположение участков, дорог и подготовить инженерные коммуникации.'],
                ['value' => '100%', 'label' => 'свободы', 'text' => 'Строительство — без подряда и типовых решений.'],
                ['value' => '10–50', 'label' => "соток\nоколо леса", 'text' => 'Мы позаботились о безопасности. Комфорт клиента — наш приоритет. Это читается в каждом метре посёлка: начиная от локации и заканчивая деталями сервиса.'],
                ['value' => '60га', 'label' => "площадь\nзастройки", 'text' => 'Мы тщательно выбирали безопасную локацию в экологически чистом районе Подмосковья в районе Можайского водохранилища в окружении леса.'],
            ],
            'seo_title' => null,
            'seo_description' => 'Опыт команды, забота о жителях и знакомство с посёлком Релакс Лэнд Можайский.',
        ];
    }
}
