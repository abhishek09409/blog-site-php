<?php

declare(strict_types=1);

class SearchController
{
    public function index(array $params): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($params['page'] ?? ($_GET['page'] ?? 1)));
        $result = ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => (int) config('app.per_page'), 'offset' => 0];
        if (mb_strlen($query) >= 2) {
            $result = Post::listing(['q' => $query], $page, (int) config('app.per_page'));
        }
        view('search/index', [
            'query' => $query,
            'posts' => $result['items'],
            'pagination' => $result,
            'base' => '/search',
            'queryParams' => $query !== '' ? ['q' => $query] : [],
            'seo' => [
                'title' => $query !== '' ? 'Search: ' . $query : 'Search',
                'description' => 'Search stories on ' . site_name() . '.',
                'robots' => 'noindex,follow',
                'canonical' => url('search') . ($query !== '' ? '?q=' . rawurlencode($query) : ''),
            ],
        ]);
    }
}
