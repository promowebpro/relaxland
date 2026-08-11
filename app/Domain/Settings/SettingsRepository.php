<?php

namespace App\Domain\Settings;

use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Schema;

class SettingsRepository
{
    private const CACHE_PREFIX = 'settings:';

    private const PUBLIC_CACHE_KEY = 'settings:public';

    public function __construct(private readonly CacheRepository $cache) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $result = $this->cache->rememberForever(
            self::CACHE_PREFIX.$key,
            function () use ($key): array {
                $setting = Setting::query()->where('key', $key)->first();

                return [
                    'found' => $setting !== null,
                    'value' => $setting?->value,
                ];
            },
        );

        return $result['found'] ? $result['value'] : $default;
    }

    public function set(
        string $key,
        mixed $value,
        string $group = 'general',
        bool $isPublic = false,
    ): Setting {
        $setting = Setting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => SettingType::fromValue($value)->value,
                'group' => $group,
                'is_public' => $isPublic,
            ],
        );

        $this->cache->forget(self::CACHE_PREFIX.$key);
        $this->cache->forget(self::PUBLIC_CACHE_KEY);

        return $setting;
    }

    public function forget(string $key): void
    {
        Setting::query()->where('key', $key)->delete();
        $this->cache->forget(self::CACHE_PREFIX.$key);
        $this->cache->forget(self::PUBLIC_CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    public function public(): array
    {
        if (! Schema::hasTable((new Setting)->getTable())) {
            return [];
        }

        return $this->cache->rememberForever(
            self::PUBLIC_CACHE_KEY,
            fn (): array => Setting::query()
                ->where('is_public', true)
                ->pluck('value', 'key')
                ->all(),
        );
    }
}
