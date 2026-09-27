<?php

declare(strict_types=1);

class SeoController
{
    public function sitemap(): void
    {
        $urls = [
            ['loc' => url(''), 'freq' => 'daily', 'priority' => '1.0'],
            ['loc' => url('blog'), 'freq' => 'daily', 'priority' => '0.9'],
            ['loc' => url('about'), 'freq' => 'monthly', 'priority' => '0.4'],
            ['loc' => url('contact'), 'freq' => 'monthly', 'priority' => '0.4'],
            ['loc' => url('privacy'), 'freq' => 'yearly', 'priority' => '0.2'],
            ['loc' => url('terms'), 'freq' => 'yearly', 'priority' => '0.2'],
        ];
        foreach (db()->fetchAll("SELECT slug, updated_at FROM categories WHERE status = 'active'") as $row) {
            $urls[] = ['loc' => url('category/' . $row['slug']), 'freq' => 'weekly', 'priority' => '0.7', 'lastmod' => $row['updated_at']];
        }
        foreach (db()->fetchAll('SELECT slug, created_at FROM tags') as $row) {
            $urls[] = ['loc' => url('tag/' . $row['slug']), 'freq' => 'weekly', 'priority' => '0.5', 'lastmod' => $row['created_at']];
        }
        foreach (db()->fetchAll("SELECT u.username, u.updated_at FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.status = 'active' AND r.name <> 'User'") as $row) {
            $urls[] = ['loc' => url('author/' . $row['username']), 'freq' => 'weekly', 'priority' => '0.5', 'lastmod' => $row['updated_at']];
        }
        $posts = db()->fetchAll(
            'SELECT slug, updated_at FROM posts p WHERE ' . Post::publicSql()
        );
        foreach ($posts as $row) {
            $urls[] = ['loc' => url('blog/' . $row['slug']), 'freq' => 'weekly', 'priority' => '0.8', 'lastmod' => $row['updated_at']];
        }
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            echo '<url><loc>' . e($url['loc']) . '</loc>';
            if (!empty($url['lastmod'])) {
                echo '<lastmod>' . e(date('c', strtotime((string) $url['lastmod']))) . '</lastmod>';
            }
            echo '<changefreq>' . e($url['freq']) . '</changefreq><priority>' . e($url['priority']) . '</priority></url>';
        }
        echo '</urlset>';
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /login\n";
        echo "Disallow: /register\n";
        echo "Disallow: /account\n";
        echo "Disallow: /forgot-password\n";
        echo "Disallow: /reset-password\n";
        echo "Disallow: /search\n";
        echo "Disallow: /api/\n";
        echo "Disallow: /preview/\n";
        echo "\nSitemap: " . url('sitemap.xml') . "\n";
    }
}
