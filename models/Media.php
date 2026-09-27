<?php

declare(strict_types=1);

class Media
{
    public static function search(string $query, bool $ownOnly, ?int $userId, int $limit = 60): array
    {
        $where = ['1=1'];
        $params = [];
        if ($ownOnly && $userId) {
            $where[] = 'user_id = ?';
            $params[] = $userId;
        }
        if ($query !== '') {
            $where[] = '(original_name LIKE ? ESCAPE \'\\\\\' OR alt_text LIKE ? ESCAPE \'\\\\\')';
            $like = like_term($query);
            $params[] = $like;
            $params[] = $like;
        }
        $limit = max(1, min(120, $limit));
        return db()->fetchAll(
            'SELECT * FROM media WHERE ' . implode(' AND ', $where) . " ORDER BY created_at DESC LIMIT {$limit}",
            $params
        );
    }

    public static function find(int $id): ?array
    {
        return db()->fetch('SELECT * FROM media WHERE id = ?', [$id]);
    }

    public static function updateAlt(int $id, string $alt): void
    {
        db()->update('media', ['alt_text' => mb_substr($alt, 0, 255)], 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        $row = self::find($id);
        if (!$row) {
            return;
        }
        Uploader::deleteFile((string) $row['filepath']);
        db()->delete('media', 'id = ?', [$id]);
    }
}
