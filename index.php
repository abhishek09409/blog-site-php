<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$method = request_method();
$rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = app_base();
$relative = $rawPath;
if ($base !== '' && ($rawPath === $base || str_starts_with($rawPath, $base . '/'))) {
    $relative = substr($rawPath, strlen($base)) ?: '/';
}
$query = $_SERVER['QUERY_STRING'] ?? '';
$path = request_path();

if ($method === 'GET' && $relative !== '/' && str_ends_with($relative, '/')) {
    redirect($path . ($query !== '' ? '?' . $query : ''), 301);
}

if ($method === 'GET') {
    $lower = mb_strtolower($path);
    if ($path !== $lower) {
        redirect($lower . ($query !== '' ? '?' . $query : ''), 301);
    }
}

$router = new Router();
require __DIR__ . '/routes/web.php';
$router->dispatch($method, $path);
