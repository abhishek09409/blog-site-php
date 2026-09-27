<?php

declare(strict_types=1);

class Category
{
    public static function active(): array
    {
        static $rows = null;
        if ($rows === null) {
            $rows = db()->fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
        }
        return $rows;
    }

    public static function all(): array
    {
        return db()->fetchAll(
            'SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id) AS post_count
             FROM categories c
             ORDER BY c.name ASC'
        );
    }

    public static function find(int $id): ?array
    {
        return db()->fetch('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return db()->fetch("SELECT * FROM categories WHERE slug = ? AND status = 'active'", [$slug]);
    }

    public static function create(array $data): int
    {
        return db()->insert('categories', $data);
    }

    public static function update(int $id, array $data): void
    {
        db()->update('categories', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        db()->delete('categories', 'id = ?', [$id]);
    }

    public static function homepageSections(int $perCategory = 3, array $exclude = []): array
    {
        $perCategory = max(1, min(6, $perCategory));
        $excludeSql = '';
        $params = [];
        if ($exclude) {
            $holders = implode(',', array_fill(0, count($exclude), '?'));
            $excludeSql = " AND p.id NOT IN ({$holders})";
            $params = array_map('intval', $exclude);
        }
        $rows = db()->fetchAll(
            "SELECT * FROM (
                SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image, p.featured_image_alt,
                       p.views, p.reading_time, p.published_at, p.category_id,
                       u.name AS author_name, u.username AS author_username, u.avatar AS author_avatar,
                       c.name AS category_name, c.slug AS category_slug, c.description AS category_description,
                       ROW_NUMBER() OVER (PARTITION BY p.category_id ORDER BY p.published_at DESC, p.id DESC) AS rn
                FROM posts p
                INNER JOIN users u ON u.id = p.author_id
                INNER JOIN categories c ON c.id = p.category_id
                WHERE " . Post::publicSql() . " AND c.status = 'active'{$excludeSql}
            ) ranked
            WHERE rn <= {$perCategory}
            ORDER BY category_name ASC, rn ASC",
            $params
        );
        $sections = [];
        foreach ($rows as $row) {
            $id = (int) $row['category_id'];
            if (!isset($sections[$id])) {
                $sections[$id] = [
                    'id' => $id,
                    'name' => $row['category_name'],
                    'slug' => $row['category_slug'],
                    'description' => $row['category_description'],
                    'posts' => [],
                ];
            }
            unset($row['rn']);
            $sections[$id]['posts'][] = $row;
        }
        return array_values($sections);
    }

    public static function popular(int $limit = 5): array
    {
        $limit = max(1, min(12, $limit));
        return db()->fetchAll(
            "SELECT c.id, c.name, c.slug, COALESCE(SUM(p.views), 0) AS views
             FROM categories c
             LEFT JOIN posts p ON p.category_id = c.id
             WHERE c.status = 'active'
             GROUP BY c.id
             ORDER BY views DESC, c.name ASC
             LIMIT {$limit}"
        );
    }
}
