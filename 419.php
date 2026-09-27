<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    require __DIR__ . '/includes/bootstrap.php';
}
http_response_code(419);
view('errors/419', [
    'seo' => ['title' => 'Page expired', 'robots' => 'noindex,nofollow'],
]);
