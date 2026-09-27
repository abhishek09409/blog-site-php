<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require __DIR__ . '/includes/gate.php';
foreach (glob(__DIR__ . '/controllers/*.php') ?: [] as $controllerFile) {
    require_once $controllerFile;
}

header('X-Robots-Tag: noindex, nofollow');

$method = request_method();
$uri = request_path();
$path = preg_replace('#^/admin#', '', $uri) ?? '';
$path = '/' . trim($path, '/');
if ($path === '/') {
    $path = '/';
}

$router = new Router();
require __DIR__ . '/routes.php';
$router->dispatch($method, $path);
