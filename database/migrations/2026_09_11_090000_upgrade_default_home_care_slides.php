<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->replace(self::legacyItems(), self::slides());
    }

    public function down(): void
    {
        $this->replace(self::slides(), self::legacyItems());
    }

    /** @param  array<int, array<string, string>>  $from
     * @param  array<int, array<string, string>>  $to
     */
    private function replace(array $from, array $to): void
    {
        DB::table('home_pages')
            ->select(['id', 'care_items'])
            ->orderBy('id')
            ->get()
            ->each(function (object $page) use ($from, $to): void {
                $items = json_decode((string) $page->care_items, true);

                if ($items !== $from) {
                    return;
                }

                DB::table('home_pages')->where('id', $page->id)->update([
                    'care_items' => json_encode($to, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
            });
    }

    /** @return array<int, array<string, string>> */
    private static function legacyItems(): array
    {
        return [
            ['title' => 'Общие пространства', 'text' => 'Уход за территорией и внимательное отношение к среде.'],
            ['title' => 'Связь с командой', 'text' => 'Понятный контакт по вопросам, которые возникают у жителей.'],
            ['title' => 'Последовательное развитие', 'text' => 'Работа по этапам без обещаний функций, которых ещё нет.'],
        ];
    }

    /** @return array<int, array<string, string>> */
    private static function slides(): array
    {
        return [
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
        ];
    }
};
