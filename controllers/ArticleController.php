<?php

declare(strict_types=1);

class ArticleController
{
    public function show(array $params): void
    {
        $post = Post::findBySlug((string) $params['slug']);
        if (!$post) {
            throw new HttpException('Article not found.', 404);
        }
        $live = Post::isLive($post);
        if (!$live && !Post::canPreview($post)) {
            throw new HttpException('Article not found.', 404);
        }
        if ($live) {
            Post::recordView((int) $post['id']);
            $post['views'] = (int) $post['views'] + (empty($_SESSION['viewed_' . $post['id']]) ? 0 : 0);
            $fresh = db()->fetch('SELECT views FROM posts WHERE id = ?', [(int) $post['id']]);
            $post['views'] = (int) ($fresh['views'] ?? $post['views']);
        }
        [$content, $toc] = prepare_article(Sanitizer::html((string) $post['content']));
        $description = $post['meta_description'] ?: excerpt_text((string) $post['excerpt'], 180);
        $image = $post['featured_image'] ? url(ltrim((string) $post['featured_image'], '/')) : url('assets/images/og-default.jpg');
        $canonical = safe_url((string) ($post['canonical_url'] ?? '')) ?: url('blog/' . $post['slug']);
        $authorUrl = url('author/' . $post['author_username']);
        view('blog/show', [
            'post' => $post,
            'content' => $content,
            'toc' => $toc,
            'related' => Post::related($post, 3),
            'comments' => Comment::treeForPost((int) $post['id']),
            'commentCount' => Comment::countApproved((int) $post['id']),
            'preview' => !$live,
            'seo' => [
                'title' => $post['meta_title'] ?: $post['title'],
                'description' => $description,
                'canonical' => $canonical,
                'image' => $image,
                'type' => 'article',
                'robots' => $live ? 'index,follow' : 'noindex,nofollow',
                'jsonld' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'Article',
                        'headline' => $post['title'],
                        'description' => $description,
                        'image' => [$image],
                        'datePublished' => date('c', strtotime((string) ($post['published_at'] ?: $post['created_at']))),
                        'dateModified' => date('c', strtotime((string) $post['updated_at'])),
                        'author' => [
                            '@type' => 'Person',
                            'name' => $post['author_name'],
                            'url' => $authorUrl,
                        ],
                        'publisher' => [
                            '@type' => 'Organization',
                            'name' => site_name(),
                            'logo' => [
                                '@type' => 'ImageObject',
                                'url' => url('assets/images/logo-mark.svg'),
                            ],
                        ],
                        'mainEntityOfPage' => $canonical,
                        'wordCount' => str_word_count(strip_tags($content)),
                    ],
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => [
                            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('')],
                            ['@type' => 'ListItem', 'position' => 2, 'name' => $post['category_name'], 'item' => url('category/' . $post['category_slug'])],
                            ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => $canonical],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function preview(array $params): void
    {
        $post = Post::find((int) $params['id']);
        if (!$post || !Post::canPreview($post)) {
            throw new HttpException('Article not found.', 404);
        }
        redirect('/blog/' . $post['slug']);
    }

    public function comment(array $params): void
    {
        $slug = (string) $params['slug'];
        $fallback = '/blog/' . $slug . '#comments';
        if (!RateLimiter::hit('comment:' . client_ip(), 5, 600)) {
            flash('error', 'Please wait a few minutes before posting another comment.');
            redirect($fallback);
        }
        Csrf::verify();
        if (!empty($_POST['company_website'])) {
            flash('success', 'Thanks. Your comment is waiting for review.');
            redirect($fallback);
        }
        $started = (int) ($_POST['form_started'] ?? 0);
        if ($started > 0 && (time() - $started) < 3) {
            flash('success', 'Thanks. Your comment is waiting for review.');
            redirect($fallback);
        }
        $post = Post::findBySlug($slug);
        if (!$post || !Post::isLive($post)) {
            throw new HttpException('Article not found.', 404);
        }
        $validator = Validator::make($_POST, [
            'name' => 'required|max:120',
            'email' => 'required|email|max:190',
            'content' => 'required|min:4|max:5000',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, $fallback);
        }
        $parentId = (int) ($_POST['parent_id'] ?? 0);
        if ($parentId > 0) {
            $parent = Comment::find($parentId);
            if (!$parent || (int) $parent['post_id'] !== (int) $post['id'] || !empty($parent['parent_id']) || $parent['status'] !== 'approved') {
                $parentId = 0;
            }
        } else {
            $parentId = 0;
        }
        $user = Auth::user();
        Comment::create([
            'post_id' => (int) $post['id'],
            'user_id' => $user['id'] ?? null,
            'name' => mb_substr(trim((string) $_POST['name']), 0, 120),
            'email' => mb_strtolower(trim((string) $_POST['email'])),
            'content' => trim((string) $_POST['content']),
            'status' => 'pending',
            'parent_id' => $parentId > 0 ? $parentId : null,
            'ip_hash' => ip_hash(),
        ]);
        flash('success', 'Thanks. Your comment will appear after a quick review.');
        redirect($fallback);
    }
}
