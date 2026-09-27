<?php

declare(strict_types=1);

return [
    'name' => 'Meridian',
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim((string) env('APP_URL', 'http://localhost:8080'), '/'),
    'key' => (string) env('APP_KEY', 'change-this-app-key'),
    'timezone' => (string) env('APP_TIMEZONE', 'UTC'),
    'per_page' => 9,
    'upload_max' => 5 * 1024 * 1024,
    'session_name' => 'meridian_session',
    'mail_enabled' => filter_var(env('MAIL_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
];
