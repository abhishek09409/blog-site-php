<?php

declare(strict_types=1);

class TagController
{
    public function show(array $params): void
    {
        $tag = Tag::findBySlug((string) $params['slug']);
        if (!$tag) {
            throw new HttpException('Tag not found.', 404);
        }
        $page = max(1, (int) ($params['page'] ?? 1));
        $result = Post::listing(['tag' => $tag['slug']], $page, (int) config('app.per_page'));
        view('tag/show', [
            'tag' => $tag,
            'posts' => $result['items'],
            'pagination' => $result,
            'base' => '/tag/' . $tag['slug'],
            'query' => [],
            'seo' => [
                'title' => $tag['name'],
                'description' => 'Stories filed under ' . $tag['name'] . ' on ' . site_name() . '.',
                'canonical' => url(ltrim(page_url('/tag/' . $tag['slug'], $result['page']), '/')),
            ],
        ]);
    }
}
