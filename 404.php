<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    require __DIR__ . '/includes/bootstrap.php';
}
http_response_code(404);
view('errors/404', [
    'seo' => ['title' => 'Page not found', 'robots' => 'noindex,follow'],
]);
