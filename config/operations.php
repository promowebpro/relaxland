<?php

return [
    'hsts_enabled' => (bool) env('HSTS_ENABLED', false),
    'hsts_max_age' => max(0, (int) env('HSTS_MAX_AGE', 31536000)),
];
