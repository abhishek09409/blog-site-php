<?php

declare(strict_types=1);

class Post
{
    public static function publicSql(string $alias = 'p'): string
    {
        return "({$alias}.status = 'published' OR ({$alias}.status = 'scheduled' AND {$alias}.published_at <= NOW()))
            AND {$alias}.visibility = 'public'
            AND ({$alias}.published_at IS NULL OR {$alias}.published_at <= NOW())";
    }

    public static function isLive(array $post): bool
    {
        if (($post['visibility'] ?? '') !== 'public') {
            return false;
        }
        $status = $post['status'] ?? '';
        $published = $post['published_at'] ?? null;
        $time = $published ? strtotime((string) $published) : time();
        if ($time > time()) {
            return false;
        }
        return $status === 'published' || $status === 'scheduled';
    }

    public static function canPreview(array $post): bool
    {
        if (!Auth::check()) {
            return false;
        }
        if (Auth::can('posts.edit')) {
            return true;
        }
        return Auth::can('posts.own') && (int) $post['author_id'] === Auth::id();
    }

    private static function listSelect(): string
    {
        return 'p.id, p.author_id, p.category_id, p.title, p.slug, p.excerpt, p.featured_image, p.featured_image_alt,
                p.status, p.visibility, p.is_featured, p.is_trending, p.views, p.reading_time, p.published_at, p.created_at,
                u.name AS author_name, u.username AS author_username, u.avatar AS author_avatar,
                c.name AS category_name, c.slug AS category_slug';
    }

    private static function from(): string
    {
        return 'FROM posts p
                INNER JOIN users u ON u.id = p.author_id
                INNER JOIN categories c ON c.id = p.category_id';
    }

    public static function withTags(array $posts): array
    {
        if (!$posts) {
            return [];
        }
        $ids = array_map(static fn (array $post): int => (int) $post['id'], $posts);
        $holders = implode(',', array_fill(0, count($ids), '?'));
        $rows = db()->fetchAll(
            "SELECT pt.post_id, t.id, t.name, t.slug
             FROM post_tags pt
             INNER JOIN tags t ON t.id = pt.tag_id
             WHERE pt.post_id IN ({$holders})
             ORDER BY t.name ASC",
            $ids
        );
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['post_id']][] = $row;
        }
        foreach ($posts as &$post) {
            $post['tags'] = $map[(int) $post['id']] ?? [];
        }
        return $posts;
    }

    public static function find(int $id): ?array
    {
        $post = db()->fetch('SELECT p.*, u.name AS author_name, u.username AS author_username FROM posts p INNER JOIN users u ON u.id = p.author_id WHERE p.id = ?', [$id]);
        if (!$post) {
            return null;
        }
        $post['tags'] = Tag::forPost($id);
        return $post;
    }

    public static function findBySlug(string $slug): ?array
    {
        $post = db()->fetch(
            'SELECT p.*, u.name AS author_name, u.username AS author_username, u.avatar AS author_avatar, u.bio AS author_bio,
                    u.website AS author_website, u.twitter AS author_twitter, u.linkedin AS author_linkedin,
                    c.name AS category_name, c.slug AS category_slug, c.description AS category_description
             ' . self::from() . ' WHERE p.slug = ?',
            [$slug]
        );
        if (!$post) {
            return null;
        }
        $post['tags'] = Tag::forPost((int) $post['id']);
        return $post;
    }

    public static function listing(array $filters, int $page, int $perPage): array
    {
        $where = [self::publicSql()];
        $params = [];
        if (!empty($filters['category'])) {
            $where[] = 'c.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['tag'])) {
            $where[] = 'EXISTS (SELECT 1 FROM post_tags pt INNER JOIN tags t ON t.id = pt.tag_id WHERE pt.post_id = p.id AND t.slug = ?)';
            $params[] = $filters['tag'];
        }
        if (!empty($filters['author'])) {
            $where[] = 'u.username = ?';
            $params[] = $filters['author'];
        }
        if (!empty($filters['q'])) {
            $words = self::searchWords((string) $filters['q']);
            foreach ($words as $word) {
                $like = like_term($word);
                $where[] = '(p.title LIKE ? ESCAPE \'\\\\\' OR p.excerpt LIKE ? ESCAPE \'\\\\\' OR p.content LIKE ? ESCAPE \'\\\\\' OR c.name LIKE ? ESCAPE \'\\\\\' OR EXISTS (
                    SELECT 1 FROM post_tags pt INNER JOIN tags t ON t.id = pt.tag_id
                    WHERE pt.post_id = p.id AND t.name LIKE ? ESCAPE \'\\\\\'
                ))';
                array_push($params, $like, $like, $like, $like, $like);
            }
        }
        $order = match ($filters['sort'] ?? 'latest') {
            'popular' => 'p.views DESC, p.published_at DESC',
            'oldest' => 'p.published_at ASC',
            'trending' => 'p.is_trending DESC, p.views DESC, p.published_at DESC',
            default => 'p.published_at DESC, p.id DESC',
        };
        $sqlWhere = implode(' AND ', $where);
        $total = (int) (db()->fetch('SELECT COUNT(*) AS c ' . self::from() . ' WHERE ' . $sqlWhere, $params)['c'] ?? 0);
        $meta = paginate($total, $page, $perPage);
        $limit = (int) $meta['per_page'];
        $offset = (int) $meta['offset'];
        $items = db()->fetchAll(
            'SELECT ' . self::listSelect() . ' ' . self::from() . ' WHERE ' . $sqlWhere . " ORDER BY {$order} LIMIT {$limit} OFFSET {$offset}",
            $params
        );
        $meta['items'] = self::withTags($items);
        return $meta;
    }

    public static function searchWords(string $query): array
    {
        $parts = preg_split('/\s+/', trim($query)) ?: [];
        $words = [];
        foreach ($parts as $part) {
            if (mb_strlen($part) >= 2) {
                $words[] = mb_substr($part, 0, 60);
            }
        }
        return array_slice($words, 0, 5);
    }

    public static function featured(int $limit = 4): array
    {
        $limit = max(1, min(8, $limit));
        return self::withTags(db()->fetchAll(
            'SELECT ' . self::listSelect() . ' ' . self::from() . ' WHERE ' . self::publicSql() . " AND p.is_featured = 1 ORDER BY p.published_at DESC LIMIT {$limit}"
        ));
    }

    public static function trending(int $limit = 4): array
    {
        $limit = max(1, min(8, $limit));
        $rows = db()->fetchAll(
            'SELECT ' . self::listSelect() . ' ' . self::from() . ' WHERE ' . self::publicSql() . " AND p.is_trending = 1 ORDER BY p.published_at DESC LIMIT {$limit}"
        );
        if (count($rows) < $limit) {
            $ids = array_column($rows, 'id') ?: [0];
            $holders = implode(',', array_fill(0, count($ids), '?'));
            $more = db()->fetchAll(
                'SELECT ' . self::listSelect() . ' ' . self::from() . ' WHERE ' . self::publicSql() . " AND p.id NOT IN ({$holders}) ORDER BY p.views DESC LIMIT " . ($limit - count($rows)),
                $ids
            );
            $rows = array_merge($rows, $more);
        }
        return self::withTags($rows);
    }

    public static function latest(int $limit = 6, array $exclude = []): array
    {
        $limit = max(1, min(24, $limit));
        $params = [];
        $sql = 'SELECT ' . self::listSelect() . ' ' . self::from() . ' WHERE ' . self::publicSql();
        if ($exclude) {
            $holders = implode(',', array_fill(0, count($exclude), '?'));
            $sql .= " AND p.id NOT IN ({$holders})";
            $params = array_map('intval', $exclude);
        }
        $sql .= " ORDER BY p.published_at DESC, p.id DESC LIMIT {$limit}";
        return self::withTags(db()->fetchAll($sql, $params));
    }

    public static function related(array $post, int $limit = 3): array
    {
        $limit = max(1, min(6, $limit));
        $tagIds = array_map(static fn (array $tag): int => (int) $tag['id'], $post['tags'] ?? []);
        $params = [(int) $post['id'], (int) $post['category_id']];
        $tagSql = '0';
        if ($tagIds) {
            $holders = implode(',', array_fill(0, count($tagIds), '?'));
            $tagSql = "EXISTS (SELECT 1 FROM post_tags pt WHERE pt.post_id = p.id AND pt.tag_id IN ({$holders}))";
            $params = array_merge($params, $tagIds);
        }
        $params[] = (int) $post['category_id'];
        return self::withTags(db()->fetchAll(
            'SELECT ' . self::listSelect() . ' ' . self::from() . '
             WHERE ' . self::publicSql() . " AND p.id <> ? AND (p.category_id = ? OR {$tagSql})
             ORDER BY (p.category_id = ?) DESC, p.published_at DESC
             LIMIT {$limit}",
            $params
        ));
    }

    public static function adminList(array $filters, int $page, bool $ownOnly, ?int $userId): array
    {
        $where = ['1=1'];
        $params = [];
        if ($ownOnly && $userId) {
            $where[] = 'p.author_id = ?';
            $params[] = $userId;
        }
        if (!empty($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[] = 'p.title LIKE ? ESCAPE \'\\\\\'';
            $params[] = like_term((string) $filters['q']);
        }
        $sqlWhere = implode(' AND ', $where);
        $total = (int) (db()->fetch('SELECT COUNT(*) AS c FROM posts p WHERE ' . $sqlWhere, $params)['c'] ?? 0);
        $meta = paginate($total, $page, 12);
        $limit = (int) $meta['per_page'];
        $offset = (int) $meta['offset'];
        $meta['items'] = db()->fetchAll(
            'SELECT p.*, u.name AS author_name, c.name AS category_name
             FROM posts p
             INNER JOIN users u ON u.id = p.author_id
             INNER JOIN categories c ON c.id = p.category_id
             WHERE ' . $sqlWhere . "
             ORDER BY p.updated_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );
        return $meta;
    }

    public static function save(array $data, array $tagIds, ?int $id = null): int
    {
        return db()->transaction(function () use ($data, $tagIds, $id): int {
            if ($id) {
                db()->update('posts', $data, 'id = ?', [$id]);
            } else {
                $id = db()->insert('posts', $data);
            }
            Tag::sync($id, $tagIds);
            return $id;
        });
    }

    public static function duplicate(int $id): int
    {
        $post = self::find($id);
        if (!$post) {
            throw new HttpException('Post not found.', 404);
        }
        $slug = unique_slug('posts', slugify($post['title'] . ' copy'));
        return db()->transaction(function () use ($post, $slug): int {
            $newId = db()->insert('posts', [
                'author_id' => (int) $post['author_id'],
                'category_id' => (int) $post['category_id'],
                'title' => 'Copy of ' . $post['title'],
                'slug' => $slug,
                'excerpt' => $post['excerpt'],
                'content' => $post['content'],
                'featured_image' => $post['featured_image'],
                'featured_image_alt' => $post['featured_image_alt'],
                'status' => 'draft',
                'visibility' => $post['visibility'],
                'is_featured' => 0,
                'is_trending' => 0,
                'views' => 0,
                'reading_time' => (int) $post['reading_time'],
                'meta_title' => $post['meta_title'],
                'meta_description' => $post['meta_description'],
                'canonical_url' => null,
                'published_at' => null,
            ]);
            Tag::sync($newId, array_map(static fn (array $tag): int => (int) $tag['id'], $post['tags']));
            return $newId;
        });
    }

    public static function delete(int $id): void
    {
        db()->delete('posts', 'id = ?', [$id]);
    }

    public static function recordView(int $postId): void
    {
        if (is_bot()) {
            return;
        }
        $key = 'viewed_' . $postId;
        if (!empty($_SESSION[$key])) {
            return;
        }
        $hash = ip_hash();
        $existing = db()->fetch(
            'SELECT id FROM post_views WHERE post_id = ? AND ip_hash = ? AND created_at > DATE_SUB(NOW(), INTERVAL 6 HOUR)',
            [$postId, $hash]
        );
        $_SESSION[$key] = 1;
        if ($existing) {
            return;
        }
        db()->insert('post_views', [
            'post_id' => $postId,
            'ip_hash' => $hash,
            'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
        db()->query('UPDATE posts SET views = views + 1 WHERE id = ?', [$postId]);
    }

    public static function counts(): array
    {
        $row = db()->fetch(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'published') AS published,
                SUM(status = 'draft') AS drafts,
                SUM(status = 'scheduled') AS scheduled,
                COALESCE(SUM(views), 0) AS views
             FROM posts"
        );
        return [
            'total' => (int) ($row['total'] ?? 0),
            'published' => (int) ($row['published'] ?? 0),
            'drafts' => (int) ($row['drafts'] ?? 0),
            'scheduled' => (int) ($row['scheduled'] ?? 0),
            'views' => (int) ($row['views'] ?? 0),
        ];
    }

    public static function viewsByDay(int $days = 14): array
    {
        $days = max(7, min(60, $days));
        $rows = db()->fetchAll(
            "SELECT DATE(created_at) AS day, COUNT(*) AS total
             FROM post_views
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL " . ($days - 1) . " DAY)
             GROUP BY DATE(created_at)"
        );
        $map = [];
        foreach ($rows as $row) {
            $map[$row['day']] = (int) $row['total'];
        }
        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime('-' . $i . ' days'));
            $series[] = [
                'date' => $day,
                'label' => date('M j', strtotime($day)),
                'count' => $map[$day] ?? 0,
            ];
        }
        return $series;
    }
}
