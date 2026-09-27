<?php

declare(strict_types=1);

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function check(?string $sent = null): bool
    {
        $sent ??= (string) ($_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        $known = $_SESSION['_csrf'] ?? '';
        return is_string($known) && $known !== '' && hash_equals($known, $sent);
    }

    public static function verify(): void
    {
        if (!self::check()) {
            throw new HttpException('The form expired. Refresh the page and try again.', 419);
        }
    }
}
