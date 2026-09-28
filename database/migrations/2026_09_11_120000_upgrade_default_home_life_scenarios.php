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
            ->select(['id', 'life_scenarios'])
            ->orderBy('id')
            ->get()
            ->each(function (object $page) use ($from, $to): void {
                $items = json_decode((string) $page->life_scenarios, true);

                if ($items !== $from) {
                    return;
                }

                DB::table('home_pages')->where('id', $page->id)->update([
                    'life_scenarios' => json_encode($to, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
            });
    }

    /** @return array<int, array<string, string>> */
    private static function legacyItems(): array
    {
        return [
            ['label' => 'Утро', 'title' => 'Начать день без спешки', 'text' => 'Открыть окно, выйти на прогулку и услышать, как просыпается природа.'],
            ['label' => 'День', 'title' => 'Быть вместе', 'text' => 'Собираться за большим столом, гулять и находить время для важных разговоров.'],
            ['label' => 'Вечер', 'title' => 'Возвращаться к себе', 'text' => 'Оставить дела на завтра и смотреть, как меняется свет над участком.'],
        ];
    }

    /** @return array<int, array<string, string>> */
    private static function slides(): array
    {
        return [
            ['label' => 'Утро', 'title' => 'Пробежка по лесу, воркаут и йога на траве', 'image_alt' => 'Пробежка по лесу'],
            ['label' => 'День', 'title' => 'Работа из коворкинга, с оптоволоконным интернетом', 'image_alt' => 'Работа в коворкинге'],
            ['label' => 'Вечер', 'title' => 'Гулять с домашним питомцем', 'image_alt' => 'Вечерняя прогулка с домашним питомцем'],
            ['title' => 'Прогулка по лесу', 'image_alt' => 'Прогулка по вечернему лесу'],
            ['label' => 'Выходные', 'title' => 'Отдых с домашним питомцем', 'image_alt' => 'Собака на лесной тропе'],
            ['title' => 'Баня', 'image_alt' => 'Банный веник в парной'],
            ['title' => 'Рыбалка', 'image_alt' => 'Рыбалка на природе'],
        ];
    }
};
