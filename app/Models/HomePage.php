<?php

namespace App\Models;

use App\Domain\Home\HomeContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomePage extends Model
{
    protected $fillable = [
        'is_active',
        'hero_eyebrow', 'hero_title', 'hero_description', 'hero_price', 'hero_image', 'hero_image_mobile', 'hero_image_alt',
        'intro_title', 'intro_text',
        'atmosphere_title', 'atmosphere_text', 'atmosphere_image', 'atmosphere_image_alt',
        'care_title', 'care_text',
        'seasons_title', 'seasons_text',
        'cta_title', 'cta_text',
        'genplan_title', 'genplan_text', 'genplan_image', 'genplan_image_alt',
        'purchase_title', 'purchase_text',
        'stories_title', 'stories_text',
        'visit_title', 'visit_text',
        'developer_title', 'developer_text', 'developer_image', 'developer_image_alt',
        'blog_title', 'blog_text',
        'benefits', 'life_scenarios', 'care_items', 'seasons', 'purchase_options',
        'seo_title', 'seo_description', 'og_image',
    ];

    protected static function booted(): void
    {
        static::saving(function (HomePage $page): void {
            $normalizer = app(HomeContent::class);

            foreach (['benefits', 'life_scenarios', 'care_items', 'seasons', 'purchase_options'] as $collection) {
                $page->{$collection} = $normalizer->sanitize($collection, $page->{$collection});
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'benefits' => 'array',
            'life_scenarios' => 'array',
            'care_items' => 'array',
            'seasons' => 'array',
            'purchase_options' => 'array',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @return array<string, mixed> */
    public static function defaultContent(): array
    {
        return [
            'is_active' => true,
            'hero_eyebrow' => 'RelaxLand Можайский',
            'hero_title' => 'Место, где начинается спокойная жизнь',
            'hero_description' => 'Загородный посёлок для тех, кто выбирает природу, пространство и понятный путь к своему дому.',
            'hero_price' => 'Участки для вашей истории',
            'intro_title' => 'Ближе к природе. Ближе к себе.',
            'intro_text' => 'RelaxLand объединяет тишину загородной жизни, заботу о территории и возможность приезжать сюда в любое время года.',
            'atmosphere_title' => 'Пространство, которое не торопит',
            'atmosphere_text' => 'Здесь день складывается из простых вещей: свежего воздуха, прогулок, встреч с близкими и времени для себя.',
            'care_title' => 'Забота о посёлке',
            'care_text' => 'Команда проекта поддерживает общие пространства и помогает жителям решать повседневные вопросы.',
            'seasons_title' => 'RelaxLand круглый год',
            'seasons_text' => 'Каждый сезон открывает свой ритм загородной жизни.',
            'cta_title' => 'Увидеть RelaxLand своими глазами',
            'cta_text' => 'Договоритесь о визите и прогуляйтесь по территории в удобное время.',
            'genplan_title' => 'Познакомьтесь с территорией',
            'genplan_text' => 'Предварительная схема помогает понять расположение улиц и общих пространств. Интерактивный генплан появится на следующем этапе.',
            'purchase_title' => 'Выберите удобный сценарий',
            'purchase_text' => 'Обсудите с отделом продаж доступные варианты и следующий шаг.',
            'stories_title' => 'Истории RelaxLand',
            'stories_text' => 'Люди, моменты и детали, из которых складывается атмосфера посёлка.',
            'visit_title' => 'Приезжайте знакомиться',
            'visit_text' => 'Расскажем о проекте, покажем территорию и ответим на вопросы без спешки.',
            'developer_title' => 'Проект с вниманием к деталям',
            'developer_text' => 'Мы развиваем RelaxLand последовательно: от инфраструктуры и общих пространств до понятного сервиса для жителей.',
            'blog_title' => 'Журнал загородной жизни',
            'blog_text' => 'Полезные материалы, идеи и новости проекта.',
            'benefits' => [
                ['title' => 'Природа рядом', 'text' => 'Пространство для прогулок, отдыха и смены привычного городского ритма.'],
                ['title' => 'Понятная среда', 'text' => 'Продуманная территория и последовательное развитие посёлка.'],
                ['title' => 'Жизнь круглый год', 'text' => 'Сценарии для выходных, каникул и постоянной загородной жизни.'],
            ],
            'life_scenarios' => [
                ['label' => 'Утро', 'title' => 'Пробежка по лесу, воркаут и йога на траве', 'image_alt' => 'Пробежка по лесу'],
                ['label' => 'День', 'title' => 'Работа из коворкинга, с оптоволоконным интернетом', 'image_alt' => 'Работа в коворкинге'],
                ['label' => 'Вечер', 'title' => 'Гулять с домашним питомцем', 'image_alt' => 'Вечерняя прогулка с домашним питомцем'],
                ['title' => 'Прогулка по лесу', 'image_alt' => 'Прогулка по вечернему лесу'],
                ['label' => 'Выходные', 'title' => 'Отдых с домашним питомцем', 'image_alt' => 'Собака на лесной тропе'],
                ['title' => 'Баня', 'image_alt' => 'Банный веник в парной'],
                ['title' => 'Рыбалка', 'image_alt' => 'Рыбалка на природе'],
            ],
            'care_items' => [
                [
                    'title' => "Уникальная служба\nзаботы 24/7*",
                    'text' => "Все необходимые мастера (электрик, сантехник, клининг, консьерж)\nВыгул собак\nСтрижка газона\nПриём доставок\nУправление участком за 5 000 ₽/мес.\nТехнадзор за строительством с регулярным видеоотчетом\nПроверенные подрядчики «Домклик»",
                    'note' => '*Сотрудники службы помогут с решением любого вопроса на участке, чтобы вы приезжали и расслаблялись, а не думали о решении бытовых вопросов по уходу за домом.',
                    'image_preset' => 'service',
                    'image_alt' => 'Белый кролик среди лесных цветов',
                    'icon' => 'heart',
                ],
                [
                    'title' => "Центральные\nкоммуникации",
                    'text' => "Электричество\nГазоснабжение\nВодоснабжение\nИнтернет\nАсфальтированные дороги\nОсвещение посёлка",
                    'image_preset' => 'utilities',
                    'image_alt' => 'Газовая конфорка',
                    'icon' => 'utilities',
                ],
                [
                    'title' => "С любовью\nк семье",
                    'text' => "Детский сад, школа\nПлощадки для всех возрастов\nПарковка на 45 м/мест\nМагазины, эко-ферма\nСадовый центр\nФельдшерский пункт\nПрогулочные зоны\nЛетний кинотеатр\nОтдельный пляж\nКафе в пляжной зоне\nЛетний кинотеатр",
                    'image_preset' => 'family',
                    'image_alt' => 'Птица в гнезде',
                    'icon' => 'home',
                ],
                [
                    'title' => "Поддерживаем\nздоровый образ жизни",
                    'text' => "Спортивные площадки, воркаут-зона\nВодный прокат: сапы, гидроциклы, катамараны\nПрокат спортивного инвентаря: снегоходы, лыжи, квадроциклы, велосипед\nВелнес-клуб (спа-зона, баня, йога, бассейн)\nПространство для ретрита\nБаскетбол, волейбол\nТеннисный корт, падел, бадминтон\nРесторан",
                    'image_preset' => 'sport',
                    'image_alt' => 'Теннисный мяч на корте',
                    'icon' => 'sport',
                ],
                [
                    'title' => 'Безопасность',
                    'text' => "Контрольно-пропускной пункт\nКруглосуточная охрана\nВидеонаблюдение 24/7\nУмный домофон",
                    'image_preset' => 'security',
                    'image_alt' => 'Камера видеонаблюдения',
                    'icon' => 'security',
                ],
            ],
            'seasons' => [
                ['label' => 'Весна', 'title' => 'Время обновления', 'text' => 'Долгие прогулки и первые планы на тёплый сезон.'],
                ['label' => 'Лето', 'title' => 'Больше жизни снаружи', 'text' => 'Завтраки на воздухе, игры и вечера, которые не хочется заканчивать.'],
                ['label' => 'Осень', 'title' => 'Тишина и цвет', 'text' => 'Спокойный ритм, лесные маршруты и уютные встречи дома.'],
                ['label' => 'Зима', 'title' => 'Свой свет в окне', 'text' => 'Снег, свежий воздух и место, куда особенно приятно возвращаться.'],
            ],
            'purchase_options' => [
                ['title' => 'Выбрать участок', 'text' => 'Сопоставить расположение, площадь и планы вашей семьи.'],
                ['title' => 'Запланировать визит', 'text' => 'Увидеть территорию и обсудить детали на месте.'],
                ['title' => 'Получить консультацию', 'text' => 'Задать вопросы отделу продаж и определить следующий шаг.'],
            ],
            'seo_title' => 'RelaxLand Можайский — загородный посёлок для спокойной жизни',
            'seo_description' => 'RelaxLand Можайский: природа, пространство и продуманная среда для загородной жизни круглый год.',
        ];
    }
}
