<?php

declare(strict_types=1);

class DashboardAdmin
{
    public function index(): void
    {
        admin_user();
        $counts = Post::counts();
        admin_view('dashboard', [
            'title' => 'Dashboard',
            'counts' => $counts,
            'comments' => (int) (db()->fetch('SELECT COUNT(*) AS c FROM comments')['c'] ?? 0),
            'users' => User::count(),
            'subscribers' => Newsletter::count(),
            'recentPosts' => db()->fetchAll(
                'SELECT p.id, p.title, p.status, p.views, p.published_at, u.name AS author_name
                 FROM posts p INNER JOIN users u ON u.id = p.author_id
                 ORDER BY p.updated_at DESC LIMIT 6'
            ),
            'recentComments' => db()->fetchAll(
                'SELECT c.id, c.name, c.status, c.content, c.created_at, p.title AS post_title
                 FROM comments c INNER JOIN posts p ON p.id = c.post_id
                 ORDER BY c.created_at DESC LIMIT 6'
            ),
            'activity' => ActivityLog::recent(8),
            'series' => Post::viewsByDay(14),
            'categories' => Category::popular(5),
            'popular' => db()->fetchAll(
                'SELECT id, title, slug, views FROM posts ORDER BY views DESC LIMIT 5'
            ),
        ]);
    }
}
