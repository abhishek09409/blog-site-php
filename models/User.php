<?php

declare(strict_types=1);

class User
{
    public static function find(int $id): ?array
    {
        return db()->fetch(
            'SELECT u.*, r.name AS role_name, r.permissions
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = ?',
            [$id]
        );
    }

    public static function findByUsername(string $username): ?array
    {
        return db()->fetch(
            "SELECT u.*, r.name AS role_name
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.username = ? AND u.status = 'active'",
            [$username]
        );
    }

    public static function findByEmail(string $email): ?array
    {
        return db()->fetch('SELECT * FROM users WHERE email = ?', [mb_strtolower($email)]);
    }

    public static function all(): array
    {
        return db()->fetchAll(
            'SELECT u.*, r.name AS role_name,
                    (SELECT COUNT(*) FROM posts p WHERE p.author_id = u.id) AS post_count
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             ORDER BY u.created_at DESC'
        );
    }

    public static function authors(): array
    {
        return db()->fetchAll(
            "SELECT u.id, u.name, u.username
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.status = 'active' AND r.name <> 'User'
             ORDER BY u.name ASC"
        );
    }

    public static function roles(): array
    {
        return db()->fetchAll('SELECT * FROM roles ORDER BY id ASC');
    }

    public static function create(array $data): int
    {
        $data['email'] = mb_strtolower($data['email']);
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return db()->insert('users', $data);
    }

    public static function update(int $id, array $data): void
    {
        if (isset($data['email'])) {
            $data['email'] = mb_strtolower($data['email']);
        }
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        db()->update('users', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        db()->delete('users', 'id = ?', [$id]);
    }

    public static function count(): int
    {
        return (int) (db()->fetch('SELECT COUNT(*) AS c FROM users')['c'] ?? 0);
    }

    public static function publishedCount(int $userId): int
    {
        return (int) (db()->fetch(
            'SELECT COUNT(*) AS c FROM posts WHERE author_id = ? AND ' . Post::publicSql('posts'),
            [$userId]
        )['c'] ?? 0);
    }
}
