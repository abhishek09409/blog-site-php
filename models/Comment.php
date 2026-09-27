<?php

declare(strict_types=1);

class Comment
{
    public static function find(int $id): ?array
    {
        return db()->fetch('SELECT * FROM comments WHERE id = ?', [$id]);
    }

    public static function treeForPost(int $postId): array
    {
        $rows = db()->fetchAll(
            "SELECT * FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC",
            [$postId]
        );
        $byParent = [];
        foreach ($rows as $row) {
            $parent = (int) ($row['parent_id'] ?? 0);
            $byParent[$parent][] = $row;
        }
        $build = static function (int $parent) use (&$build, $byParent): array {
            $branch = [];
            foreach ($byParent[$parent] ?? [] as $comment) {
                $comment['replies'] = $build((int) $comment['id']);
                $branch[] = $comment;
            }
            return $branch;
        };
        return $build(0);
    }

    public static function countApproved(int $postId): int
    {
        return (int) (db()->fetch(
            "SELECT COUNT(*) AS c FROM comments WHERE post_id = ? AND status = 'approved'",
            [$postId]
        )['c'] ?? 0);
    }

    public static function create(array $data): int
    {
        return db()->insert('comments', $data);
    }

    public static function adminList(string $status, bool $ownOnly, ?int $userId): array
    {
        $where = ['1=1'];
        $params = [];
        if ($status !== '' && $status !== 'all') {
            $where[] = 'c.status = ?';
            $params[] = $status;
        }
        if ($ownOnly && $userId) {
            $where[] = 'p.author_id = ?';
            $params[] = $userId;
        }
        return db()->fetchAll(
            'SELECT c.*, p.title AS post_title, p.slug AS post_slug
             FROM comments c
             INNER JOIN posts p ON p.id = c.post_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY c.created_at DESC
             LIMIT 200',
            $params
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        db()->update('comments', ['status' => $status], 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        db()->delete('comments', 'id = ?', [$id]);
    }
}
