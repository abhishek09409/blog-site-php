<?php

declare(strict_types=1);

class PostAdmin
{
    public function index(): void
    {
        $user = admin_require('posts.edit', 'posts.own');
        $ownOnly = !Auth::can('posts.edit');
        $status = (string) ($_GET['status'] ?? '');
        if (!in_array($status, ['', 'draft', 'published', 'scheduled', 'unpublished'], true)) {
            $status = '';
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = Post::adminList([
            'status' => $status,
            'q' => trim((string) ($_GET['q'] ?? '')),
        ], $page, $ownOnly, (int) $user['id']);
        admin_view('posts/index', [
            'title' => 'Posts',
            'result' => $result,
            'status' => $status,
            'q' => trim((string) ($_GET['q'] ?? '')),
        ]);
    }

    public function create(): void
    {
        admin_require('posts.edit', 'posts.own');
        admin_view('posts/form', $this->formData(null));
    }

    public function store(): void
    {
        $user = admin_require('posts.edit', 'posts.own');
        Csrf::verify();
        $id = $this->persist(null, $user);
        flash('success', 'Post saved.');
        redirect('/admin/posts/edit/' . $id);
    }

    public function edit(array $params): void
    {
        $post = $this->authorizedPost((int) $params['id']);
        admin_view('posts/form', $this->formData($post));
    }

    public function update(array $params): void
    {
        $user = admin_user();
        $post = $this->authorizedPost((int) $params['id']);
        Csrf::verify();
        $this->persist((int) $post['id'], $user);
        flash('success', 'Post updated.');
        redirect('/admin/posts/edit/' . $post['id']);
    }

    public function delete(array $params): void
    {
        Csrf::verify();
        $post = $this->authorizedPost((int) $params['id']);
        Post::delete((int) $post['id']);
        ActivityLog::record('post.delete', 'Deleted “' . $post['title'] . '”');
        flash('success', 'Post deleted.');
        redirect('/admin/posts');
    }

    public function duplicate(array $params): void
    {
        Csrf::verify();
        $post = $this->authorizedPost((int) $params['id']);
        $newId = Post::duplicate((int) $post['id']);
        if (!Auth::can('posts.edit')) {
            db()->update('posts', ['author_id' => Auth::id()], 'id = ?', [$newId]);
        }
        ActivityLog::record('post.duplicate', 'Duplicated “' . $post['title'] . '”');
        flash('success', 'Draft copy created.');
        redirect('/admin/posts/edit/' . $newId);
    }

    private function formData(?array $post): array
    {
        return [
            'title' => $post ? 'Edit post' : 'Add new post',
            'post' => $post,
            'categories' => Category::all(),
            'tags' => Tag::all(),
            'authors' => User::authors(),
            'media' => Media::search('', !Auth::can('media.view') && !Auth::can('posts.edit'), Auth::id(), 48),
            'canAssign' => Auth::can('posts.edit'),
        ];
    }

    private function authorizedPost(int $id): array
    {
        admin_require('posts.edit', 'posts.own');
        $post = Post::find($id);
        if (!$post) {
            throw new HttpException('Post not found.', 404);
        }
        if (!Auth::can('posts.edit') && (int) $post['author_id'] !== Auth::id()) {
            throw new HttpException('You can only edit your own posts.', 403);
        }
        return $post;
    }

    private function persist(?int $id, array $user): int
    {
        $validator = Validator::make($_POST, [
            'title' => 'required|max:220',
            'excerpt' => 'max:500',
            'content' => 'required|min:2',
            'category_id' => 'required|int',
            'status' => 'required|in:draft,published,scheduled,unpublished',
            'visibility' => 'required|in:public,private',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, $id ? '/admin/posts/edit/' . $id : '/admin/posts/create');
        }
        $category = Category::find((int) $_POST['category_id']);
        if (!$category) {
            back_with_errors(['category_id' => ['Choose a category.']], $_POST, '/admin/posts');
        }
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $slug = unique_slug('posts', slugify($slugInput !== '' ? $slugInput : (string) $_POST['title']), $id);
        if (in_array($slug, ['page', 'feed', 'preview'], true)) {
            $slug = unique_slug('posts', $slug . '-story', $id);
        }
        $content = Sanitizer::html((string) $_POST['content']);
        if (trim(strip_tags($content)) === '' && !str_contains($content, '<img') && !str_contains($content, '<iframe')) {
            back_with_errors(['content' => ['Write something before saving.']], $_POST, '/admin/posts');
        }
        $status = (string) $_POST['status'];
        $publishedAt = trim((string) ($_POST['published_at'] ?? ''));
        $publishedAt = $publishedAt !== '' ? str_replace('T', ' ', $publishedAt) : null;
        if ($publishedAt && !strtotime($publishedAt)) {
            $publishedAt = null;
        }
        if ($status === 'published' && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }
        if ($status === 'published' && $publishedAt && strtotime($publishedAt) > time()) {
            $status = 'scheduled';
        }
        if ($status === 'scheduled' && (!$publishedAt || strtotime((string) $publishedAt) <= time())) {
            $status = 'published';
            $publishedAt = $publishedAt ?: date('Y-m-d H:i:s');
        }
        $authorId = (int) $user['id'];
        if (Auth::can('posts.edit') && !empty($_POST['author_id'])) {
            $author = User::find((int) $_POST['author_id']);
            if ($author) {
                $authorId = (int) $author['id'];
            }
        } elseif ($id) {
            $existing = Post::find($id);
            $authorId = (int) ($existing['author_id'] ?? $authorId);
        }
        $image = trim((string) ($_POST['featured_image'] ?? ''));
        if (!empty($_FILES['featured_upload']['name'])) {
            try {
                $media = Uploader::storeImage($_FILES['featured_upload'], (int) $user['id'], trim((string) ($_POST['featured_image_alt'] ?? '')));
                $image = $media['filepath'];
            } catch (RuntimeException $e) {
                back_with_errors(['featured_image' => [$e->getMessage()]], $_POST, '/admin/posts');
            }
        }
        if ($image !== '' && !preg_match('#^/(uploads|assets)/#', $image)) {
            $image = '';
        }
        $canonical = trim((string) ($_POST['canonical_url'] ?? ''));
        if ($canonical !== '' && !safe_url($canonical)) {
            back_with_errors(['canonical_url' => ['Enter a valid canonical URL.']], $_POST, '/admin/posts');
        }
        $data = [
            'author_id' => $authorId,
            'category_id' => (int) $category['id'],
            'title' => trim((string) $_POST['title']),
            'slug' => $slug,
            'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
            'content' => $content,
            'featured_image' => $image !== '' ? $image : null,
            'featured_image_alt' => trim((string) ($_POST['featured_image_alt'] ?? '')) ?: null,
            'status' => $status,
            'visibility' => (string) $_POST['visibility'],
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'is_trending' => !empty($_POST['is_trending']) ? 1 : 0,
            'reading_time' => reading_time($content),
            'meta_title' => trim((string) ($_POST['meta_title'] ?? '')) ?: null,
            'meta_description' => trim((string) ($_POST['meta_description'] ?? '')) ?: null,
            'canonical_url' => $canonical !== '' ? $canonical : null,
            'published_at' => $publishedAt,
        ];
        if (!$id) {
            $data['views'] = 0;
        }
        $tagIds = array_map('intval', $_POST['tags'] ?? []);
        $saved = Post::save($data, $tagIds, $id);
        ActivityLog::record($id ? 'post.update' : 'post.create', ($id ? 'Updated' : 'Created') . ' “' . $data['title'] . '”');
        return $saved;
    }
}
