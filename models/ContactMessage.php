<?php

declare(strict_types=1);

class ContactMessage
{
    public static function create(array $data): int
    {
        return db()->insert('contact_messages', $data);
    }

    public static function all(): array
    {
        return db()->fetchAll('SELECT * FROM contact_messages ORDER BY created_at DESC');
    }

    public static function find(int $id): ?array
    {
        return db()->fetch('SELECT * FROM contact_messages WHERE id = ?', [$id]);
    }

    public static function markRead(int $id): void
    {
        db()->update('contact_messages', ['status' => 'read'], 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        db()->delete('contact_messages', 'id = ?', [$id]);
    }

    public static function count(): int
    {
        return (int) (db()->fetch('SELECT COUNT(*) AS c FROM contact_messages')['c'] ?? 0);
    }
}
