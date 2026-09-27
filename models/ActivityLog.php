<?php

declare(strict_types=1);

class ActivityLog
{
    public static function record(string $action, string $description, ?int $userId = null): void
    {
        try {
            db()->insert('activity_logs', [
                'user_id' => $userId ?? Auth::id(),
                'action' => mb_substr($action, 0, 80),
                'description' => mb_substr($description, 0, 255),
                'ip_address' => client_ip(),
            ]);
        } catch (Throwable) {
        }
    }

    public static function recent(int $limit = 8): array
    {
        $limit = max(1, min(50, $limit));
        return db()->fetchAll(
            "SELECT a.*, u.name AS user_name
             FROM activity_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT {$limit}"
        );
    }
}
