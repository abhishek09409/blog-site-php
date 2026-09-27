<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

if (request_method() !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}
$data = request_data();
if (!Csrf::check((string) ($data['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')))) {
    json_response(['ok' => false, 'message' => 'Invalid token.'], 419);
}
if (!RateLimiter::hit('view:' . client_ip(), 60, 600)) {
    json_response(['ok' => true, 'counted' => false]);
}
$id = (int) ($data['post_id'] ?? 0);
$post = $id > 0 ? Post::find($id) : null;
if (!$post || !Post::isLive($post)) {
    json_response(['ok' => false, 'message' => 'Article not found.'], 404);
}
$before = (int) $post['views'];
Post::recordView($id);
$after = (int) (db()->fetch('SELECT views FROM posts WHERE id = ?', [$id])['views'] ?? $before);
json_response(['ok' => true, 'views' => $after, 'counted' => $after > $before]);
