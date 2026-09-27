<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/includes/helpers.php';
require BASE_PATH . '/includes/HttpException.php';

$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}

date_default_timezone_set((string) config('app.timezone'));

if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

require BASE_PATH . '/includes/Database.php';
require BASE_PATH . '/includes/Logger.php';
require BASE_PATH . '/includes/Csrf.php';
require BASE_PATH . '/includes/RateLimiter.php';
require BASE_PATH . '/includes/Mailer.php';
require BASE_PATH . '/includes/Sanitizer.php';
require BASE_PATH . '/includes/Validator.php';
require BASE_PATH . '/includes/Uploader.php';

spl_autoload_register(static function (string $class): void {
    foreach (['models', 'controllers', 'admin/controllers', 'includes'] as $dir) {
        $file = BASE_PATH . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

set_exception_handler(static function (Throwable $e): void {
    if (!$e instanceof HttpException || $e->status >= 500) {
        Logger::exception($e);
    }
    $status = $e instanceof HttpException ? $e->status : 500;
    if (!headers_sent()) {
        http_response_code($status);
    }
    $dbDown = $e instanceof PDOException || $e->getPrevious() instanceof PDOException;
    if (config('app.debug')) {
        $detail = e($e::class . ': ' . $e->getMessage());
        echo '<!doctype html><meta charset="utf-8"><title>Application error</title><body style="font-family:Georgia,serif;padding:40px;background:#F8FAFC;color:#111827">';
        echo '<h1 style="font-weight:500">Application error</h1><p>' . $detail . '</p>';
        if ($dbDown) {
            echo '<p>Check <code>.env</code> and import <code>database/schema.sql</code> plus <code>database/seed.sql</code>.</p>';
        }
        echo '<pre style="white-space:pre-wrap">' . e($e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString()) . '</pre></body>';
        return;
    }
    $file = match ($status) {
        403 => '403.php',
        404 => '404.php',
        419 => '419.php',
        default => '500.php',
    };
    $page = BASE_PATH . '/' . $file;
    if (is_file($page) && empty($GLOBALS['rendering_error'])) {
        $GLOBALS['rendering_error'] = true;
        require $page;
        return;
    }
    echo 'Something went wrong.';
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name((string) config('app.session_name'));
session_start();

require BASE_PATH . '/includes/Auth.php';

try {
    send_security_headers();
    Auth::boot();
} catch (Throwable $e) {
    Logger::exception($e);
}
