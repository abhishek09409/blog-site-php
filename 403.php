<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    require __DIR__ . '/includes/bootstrap.php';
}
http_response_code(403);
view('errors/403', [
    'seo' => ['title' => 'Forbidden', 'robots' => 'noindex,nofollow'],
]);
