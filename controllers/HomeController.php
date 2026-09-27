<?php

declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        $featured = Post::featured(4);
        if (count($featured) < 4) {
            $featured = array_merge($featured, Post::latest(4 - count($featured), array_column($featured, 'id')));
        }
        $featured = array_slice($featured, 0, 4);
        $trending = Post::trending(4);
        $exclude = array_values(array_unique(array_merge(
            array_map('intval', array_column($featured, 'id')),
            array_map('intval', array_column($trending, 'id'))
        )));
        $description = setting('seo_description', setting('site_description', 'Reporting and essays on technology, business, and the way we work.'));
        view('home', [
            'hero' => $featured[0] ?? null,
            'secondary' => array_slice($featured, 1, 3),
            'trending' => $trending,
            'latest' => Post::latest(6, $exclude),
            'sections' => Category::homepageSections(3, $exclude),
            'seo' => [
                'title' => setting('seo_title', site_name()),
                'description' => $description,
                'canonical' => url(''),
                'type' => 'website',
                'jsonld' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'Organization',
                        'name' => site_name(),
                        'url' => url(''),
                        'description' => $description,
                        'email' => setting('email', ''),
                        'logo' => url('assets/images/logo-mark.svg'),
                    ],
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'WebSite',
                        'name' => site_name(),
                        'url' => url(''),
                        'potentialAction' => [
                            '@type' => 'SearchAction',
                            'target' => url('search') . '?q={query}',
                            'query-input' => 'required name=query',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
