<?php

return [
    'public_url' => env('SEO_PUBLIC_URL', env('APP_URL', 'http://localhost')),
    'indexing_enabled' => (bool) env('SEO_INDEXING_ENABLED', false),
    'default_locale' => env('SEO_DEFAULT_LOCALE', 'ru_RU'),
];
