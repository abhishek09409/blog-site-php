<?php

declare(strict_types=1);

class CategoryController
{
    public function show(array $params): void
    {
        $category = Category::findBySlug((string) $params['slug']);
        if (!$category) {
            throw new HttpException('Category not found.', 404);
        }
        $page = max(1, (int) ($params['page'] ?? 1));
        $result = Post::listing(['category' => $category['slug']], $page, (int) config('app.per_page'));
        $description = $category['meta_description'] ?: excerpt_text((string) $category['description'], 180);
        view('category/show', [
            'category' => $category,
            'posts' => $result['items'],
            'pagination' => $result,
            'base' => '/category/' . $category['slug'],
            'query' => [],
            'seo' => [
                'title' => $category['meta_title'] ?: $category['name'],
                'description' => $description,
                'canonical' => url(ltrim(page_url('/category/' . $category['slug'], $result['page']), '/')),
                'jsonld' => [[
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => $category['name'],
                    'description' => $description,
                    'url' => url('category/' . $category['slug']),
                ]],
            ],
        ]);
    }
}
