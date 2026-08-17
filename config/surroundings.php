<?php

return [
    'provider' => 'yandex-js-v3',
    'enabled' => env('SURROUNDINGS_MAP_ENABLED', true),
    'api_key' => env('YANDEX_MAPS_API_KEY'),
    'locale' => env('SURROUNDINGS_MAP_LOCALE', 'ru_RU'),
    'default_zoom' => (int) env('SURROUNDINGS_MAP_DEFAULT_ZOOM', 11),
    'min_zoom' => 7,
    'max_zoom' => 17,
    'theme' => env('SURROUNDINGS_MAP_THEME', 'light'),
    'timeout_ms' => (int) env('SURROUNDINGS_MAP_TIMEOUT_MS', 10000),
    'sdk_url' => 'https://api-maps.yandex.ru/v3/',
];
