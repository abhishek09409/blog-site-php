<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$method = request_method();
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

if ($method === 'GET' && $path !== '/' && str_ends_with($path, '/')) {
    $query = $_SERVER['QUERY_STRING'] ?? '';
    redirect(rtrim($path, '/') . ($query !== '' ? '?' . $query : ''), 301);
}

if ($method === 'GET') {
    $lower = mb_strtolower($path);
    if ($path !== $lower) {
        $query = $_SERVER['QUERY_STRING'] ?? '';
        redirect($lower . ($query !== '' ? '?' . $query : ''), 301);
    }
}

$router = new Router();
require __DIR__ . '/routes/web.php';
$router->dispatch($method, $uri);
