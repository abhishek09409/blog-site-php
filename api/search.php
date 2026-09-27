<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$query = trim((string) ($_GET['q'] ?? ''));
if (mb_strlen($query) < 2) {
    json_response(['results' => []]);
}
$result = Post::listing(['q' => $query], 1, 6);
$items = [];
foreach ($result['items'] as $post) {
    $items[] = [
        'title' => $post['title'],
        'url' => '/blog/' . $post['slug'],
        'category' => $post['category_name'],
        'date' => format_date((string) $post['published_at'], 'M j, Y'),
    ];
}
json_response(['results' => $items, 'total' => $result['total']]);
