<?php

declare(strict_types=1);

class Newsletter
{
    public static function subscribe(string $email): string
    {
        $email = mb_strtolower(trim($email));
        $existing = db()->fetch('SELECT * FROM newsletter_subscribers WHERE email = ?', [$email]);
        if ($existing && $existing['status'] === 'active') {
            return 'already';
        }
        if ($existing) {
            db()->update('newsletter_subscribers', ['status' => 'active'], 'id = ?', [(int) $existing['id']]);
            return 'resubscribed';
        }
        db()->insert('newsletter_subscribers', ['email' => $email, 'status' => 'active']);
        return 'subscribed';
    }

    public static function all(): array
    {
        return db()->fetchAll('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC');
    }

    public static function count(): int
    {
        return (int) (db()->fetch("SELECT COUNT(*) AS c FROM newsletter_subscribers WHERE status = 'active'")['c'] ?? 0);
    }

    public static function setStatus(int $id, string $status): void
    {
        db()->update('newsletter_subscribers', ['status' => $status], 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        db()->delete('newsletter_subscribers', 'id = ?', [$id]);
    }
}
