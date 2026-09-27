<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

if (request_method() !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$data = request_data();
if (!Csrf::check((string) ($data['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')))) {
    json_response(['ok' => false, 'message' => 'The form expired. Refresh and try again.'], 419);
}
if (!RateLimiter::hit('comment:' . client_ip(), 5, 600)) {
    json_response(['ok' => false, 'message' => 'Please wait before posting another comment.'], 429);
}
if (!empty($data['company_website'])) {
    json_response(['ok' => true, 'message' => 'Thanks. Your comment is waiting for review.']);
}

$slug = (string) ($data['slug'] ?? '');
$post = $slug !== '' ? Post::findBySlug($slug) : null;
if (!$post || !Post::isLive($post)) {
    json_response(['ok' => false, 'message' => 'Article not found.'], 404);
}
$validator = Validator::make($data, [
    'name' => 'required|max:120',
    'email' => 'required|email|max:190',
    'content' => 'required|min:4|max:5000',
]);
if ($validator->fails()) {
    json_response(['ok' => false, 'message' => 'Check the comment form and try again.', 'errors' => $validator->errors()], 422);
}
$user = Auth::user();
Comment::create([
    'post_id' => (int) $post['id'],
    'user_id' => $user['id'] ?? null,
    'name' => mb_substr(trim((string) $data['name']), 0, 120),
    'email' => mb_strtolower(trim((string) $data['email'])),
    'content' => trim((string) $data['content']),
    'status' => 'pending',
    'parent_id' => null,
    'ip_hash' => ip_hash(),
]);
json_response(['ok' => true, 'message' => 'Thanks. Your comment will appear after a quick review.']);
