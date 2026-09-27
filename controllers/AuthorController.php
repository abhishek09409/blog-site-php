<?php

declare(strict_types=1);

class AuthorController
{
    public function show(array $params): void
    {
        $author = User::findByUsername((string) $params['slug']);
        if (!$author || ($author['role_name'] ?? '') === 'User') {
            throw new HttpException('Author not found.', 404);
        }
        $page = max(1, (int) ($params['page'] ?? 1));
        $result = Post::listing(['author' => $author['username']], $page, (int) config('app.per_page'));
        $description = excerpt_text((string) ($author['bio'] ?: $author['name'] . ' writes for ' . site_name() . '.'), 180);
        $image = !empty($author['avatar']) ? url(ltrim((string) $author['avatar'], '/')) : url('assets/images/og-default.jpg');
        view('author/show', [
            'author' => $author,
            'posts' => $result['items'],
            'pagination' => $result,
            'totalArticles' => User::publishedCount((int) $author['id']),
            'base' => '/author/' . $author['username'],
            'query' => [],
            'seo' => [
                'title' => $author['name'],
                'description' => $description,
                'image' => $image,
                'canonical' => url(ltrim(page_url('/author/' . $author['username'], $result['page']), '/')),
                'jsonld' => [[
                    '@context' => 'https://schema.org',
                    '@type' => 'Person',
                    'name' => $author['name'],
                    'description' => $description,
                    'url' => url('author/' . $author['username']),
                    'image' => $image,
                    'sameAs' => array_values(array_filter([
                        safe_url((string) ($author['website'] ?? '')),
                        !empty($author['twitter']) ? 'https://x.com/' . ltrim((string) $author['twitter'], '@') : null,
                        safe_url((string) ($author['linkedin'] ?? '')),
                    ])),
                ]],
            ],
        ]);
    }
}
