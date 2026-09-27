<?php

declare(strict_types=1);

class BlogController
{
    public function index(array $params): void
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $sort = (string) ($_GET['sort'] ?? 'latest');
        if (!in_array($sort, ['latest', 'popular', 'oldest', 'trending'], true)) {
            $sort = 'latest';
        }
        $result = Post::listing(['sort' => $sort], $page, (int) config('app.per_page'));
        $canonical = url(ltrim(page_url('/blog', $result['page'], $sort === 'latest' ? [] : ['sort' => $sort]), '/'));
        view('blog/index', [
            'posts' => $result['items'],
            'pagination' => $result,
            'sort' => $sort,
            'base' => '/blog',
            'query' => $sort === 'latest' ? [] : ['sort' => $sort],
            'tags' => Tag::popular(10),
            'heading' => 'The Journal',
            'intro' => 'Essays, reporting, and field notes from the Meridian desk.',
            'seo' => [
                'title' => 'The Journal',
                'description' => 'Browse the latest stories from Meridian on technology, business, finance, lifestyle, and travel.',
                'canonical' => $canonical,
            ],
        ]);
    }
}
