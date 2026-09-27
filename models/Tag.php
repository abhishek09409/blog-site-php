<?php

declare(strict_types=1);

class Tag
{
    public static function all(): array
    {
        return db()->fetchAll(
            'SELECT t.*, (SELECT COUNT(*) FROM post_tags pt WHERE pt.tag_id = t.id) AS post_count
             FROM tags t
             ORDER BY t.name ASC'
        );
    }

    public static function popular(int $limit = 12): array
    {
        $limit = max(1, min(40, $limit));
        return db()->fetchAll(
            "SELECT t.*, COUNT(pt.post_id) AS post_count
             FROM tags t
             INNER JOIN post_tags pt ON pt.tag_id = t.id
             INNER JOIN posts p ON p.id = pt.post_id
             WHERE " . Post::publicSql() . "
             GROUP BY t.id
             ORDER BY post_count DESC, t.name ASC
             LIMIT {$limit}"
        );
    }

    public static function find(int $id): ?array
    {
        return db()->fetch('SELECT * FROM tags WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return db()->fetch('SELECT * FROM tags WHERE slug = ?', [$slug]);
    }

    public static function create(array $data): int
    {
        return db()->insert('tags', $data);
    }

    public static function update(int $id, array $data): void
    {
        db()->update('tags', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        db()->delete('tags', 'id = ?', [$id]);
    }

    public static function forPost(int $postId): array
    {
        return db()->fetchAll(
            'SELECT t.* FROM tags t
             INNER JOIN post_tags pt ON pt.tag_id = t.id
             WHERE pt.post_id = ?
             ORDER BY t.name ASC',
            [$postId]
        );
    }

    public static function sync(int $postId, array $tagIds): void
    {
        db()->delete('post_tags', 'post_id = ?', [$postId]);
        foreach (array_unique(array_map('intval', $tagIds)) as $tagId) {
            if ($tagId > 0 && self::find($tagId)) {
                db()->insert('post_tags', ['post_id' => $postId, 'tag_id' => $tagId]);
            }
        }
    }
}
