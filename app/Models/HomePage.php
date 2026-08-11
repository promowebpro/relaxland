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
                ['label' => 'Утро', 'title' => 'Начать день без спешки', 'text' => 'Открыть окно, выйти на прогулку и услышать, как просыпается природа.'],
                ['label' => 'День', 'title' => 'Быть вместе', 'text' => 'Собираться за большим столом, гулять и находить время для важных разговоров.'],
                ['label' => 'Вечер', 'title' => 'Возвращаться к себе', 'text' => 'Оставить дела на завтра и смотреть, как меняется свет над участком.'],
            ],
            'care_items' => [
                ['title' => 'Общие пространства', 'text' => 'Уход за территорией и внимательное отношение к среде.'],
                ['title' => 'Связь с командой', 'text' => 'Понятный контакт по вопросам, которые возникают у жителей.'],
                ['title' => 'Последовательное развитие', 'text' => 'Работа по этапам без обещаний функций, которых ещё нет.'],
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
