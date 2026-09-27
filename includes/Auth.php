<?php

declare(strict_types=1);

class Auth
{
    private const REMEMBER_COOKIE = 'meridian_remember';

    public static function boot(): void
    {
        if (!empty($_SESSION['_regen']) && (time() - (int) $_SESSION['_regen']) > 1800) {
            session_regenerate_id(true);
            $_SESSION['_regen'] = time();
        }
        if (empty($_SESSION['user_id'])) {
            self::loginFromRemember();
        }
    }

    public static function user(): ?array
    {
        static $cached = false;
        static $user = null;
        $sessionId = $_SESSION['user_id'] ?? null;
        if ($cached && ($user['id'] ?? null) == $sessionId) {
            return $user;
        }
        if (!$sessionId) {
            $cached = true;
            $user = null;
            return null;
        }
        $user = db()->fetch(
            'SELECT u.*, r.name AS role_name, r.permissions
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.status = ?',
            [(int) $sessionId, 'active']
        );
        if (!$user) {
            unset($_SESSION['user_id']);
        }
        $cached = true;
        return $user ?: null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        $permissions = json_decode((string) $user['permissions'], true);
        if (!is_array($permissions)) {
            return false;
        }
        if (in_array('*', $permissions, true) || in_array($permission, $permissions, true)) {
            return true;
        }
        foreach ($permissions as $granted) {
            if (!is_string($granted) || !str_ends_with($granted, '.*')) {
                continue;
            }
            $prefix = substr($granted, 0, -1);
            if (str_starts_with($permission, $prefix)) {
                return true;
            }
        }
        return false;
    }

    public static function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }
        return false;
    }

    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        $email = mb_strtolower(trim($email));
        $ip = client_ip();
        $recent = db()->fetch(
            'SELECT COUNT(*) AS c FROM login_attempts
             WHERE (email = ? OR ip_address = ?)
               AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)',
            [$email, $ip]
        );
        if ((int) ($recent['c'] ?? 0) >= 5) {
            throw new HttpException('Too many sign-in attempts. Try again in a few minutes.', 429);
        }
        $user = db()->fetch('SELECT * FROM users WHERE email = ?', [$email]);
        $valid = $user && password_verify($password, (string) $user['password']);
        if (!$valid || ($user['status'] ?? '') !== 'active') {
            db()->insert('login_attempts', ['email' => $email, 'ip_address' => $ip]);
            return false;
        }
        if (password_needs_rehash((string) $user['password'], PASSWORD_DEFAULT)) {
            db()->update('users', ['password' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [(int) $user['id']]);
        }
        self::loginUser((int) $user['id'], $remember);
        db()->delete('login_attempts', 'email = ?', [$email]);
        db()->update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $user['id']]);
        ActivityLog::record('auth.login', 'Signed in', (int) $user['id']);
        return true;
    }

    public static function loginUser(int $userId, bool $remember = false): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['_regen'] = time();
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        if ($remember) {
            self::remember($userId);
        }
    }

    public static function logout(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            ActivityLog::record('auth.logout', 'Signed out', (int) $userId);
        }
        self::forgetRemember();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], true);
        }
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    private static function remember(int $userId): void
    {
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        db()->delete('remember_tokens', 'user_id = ?', [$userId]);
        db()->insert('remember_tokens', [
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => hash('sha256', $validator),
            'expires_at' => date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30),
        ]);
        self::cookie(self::REMEMBER_COOKIE, $selector . ':' . $validator, time() + 60 * 60 * 24 * 30);
    }

    private static function loginFromRemember(): void
    {
        $raw = $_COOKIE[self::REMEMBER_COOKIE] ?? '';
        if (!is_string($raw) || !str_contains($raw, ':')) {
            return;
        }
        [$selector, $validator] = explode(':', $raw, 2);
        if (!preg_match('/^[a-f0-9]{24}$/', $selector)) {
            self::forgetRemember();
            return;
        }
        $row = db()->fetch('SELECT * FROM remember_tokens WHERE selector = ? AND expires_at > NOW()', [$selector]);
        if (!$row || !hash_equals($row['token_hash'], hash('sha256', $validator))) {
            if ($row) {
                db()->delete('remember_tokens', 'user_id = ?', [(int) $row['user_id']]);
            }
            self::forgetRemember();
            return;
        }
        $user = db()->fetch('SELECT id FROM users WHERE id = ? AND status = ?', [(int) $row['user_id'], 'active']);
        if (!$user) {
            self::forgetRemember();
            return;
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['_regen'] = time();
        self::remember((int) $user['id']);
    }

    private static function forgetRemember(): void
    {
        $raw = $_COOKIE[self::REMEMBER_COOKIE] ?? '';
        if (is_string($raw) && str_contains($raw, ':')) {
            [$selector] = explode(':', $raw, 2);
            if (preg_match('/^[a-f0-9]{24}$/', $selector)) {
                db()->delete('remember_tokens', 'selector = ?', [$selector]);
            }
        }
        self::cookie(self::REMEMBER_COOKIE, '', time() - 42000);
    }

    private static function cookie(string $name, string $value, int $expires): void
    {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie($name, $value, [
            'expires' => $expires,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
