<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    require __DIR__ . '/includes/bootstrap.php';
}
http_response_code(500);
view('errors/500', [
    'seo' => ['title' => 'Something went wrong', 'robots' => 'noindex,nofollow'],
]);
