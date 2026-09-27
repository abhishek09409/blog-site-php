<?php

declare(strict_types=1);

class RateLimiter
{
    public static function hit(string $key, int $max, int $seconds): bool
    {
        $hash = hash('sha256', $key);
        $seconds = max(1, $seconds);
        $row = db()->fetch(
            "SELECT COUNT(*) AS c FROM rate_limits WHERE rate_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL {$seconds} SECOND)",
            [$hash]
        );
        if ((int) ($row['c'] ?? 0) >= $max) {
            return false;
        }
        db()->insert('rate_limits', ['rate_key' => $hash]);
        if (random_int(1, 25) === 1) {
            db()->query('DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 DAY)');
        }
        return true;
    }
}
