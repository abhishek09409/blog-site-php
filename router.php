<?php

declare(strict_types=1);

/**
 * Router for the PHP built-in server. Apache uses .htaccess instead.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$local = __DIR__ . $uri;

if (preg_match('#^/(?:\.env|config|includes|controllers|models|views|routes|database|storage|bin|tests|admin/(?:controllers|views|includes|routes))(?:/|$)#', $uri)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden\n";
    return true;
}

if ($uri !== '/' && is_file($local)) {
    return false;
}

if (preg_match('#^/admin(?:/|$)#', $uri)) {
    require __DIR__ . '/admin/index.php';
    return true;
}

if (preg_match('#^/api/[a-z0-9_-]+\.php$#', $uri) && is_file($local)) {
    require $local;
    return true;
}

require __DIR__ . '/index.php';
return true;
