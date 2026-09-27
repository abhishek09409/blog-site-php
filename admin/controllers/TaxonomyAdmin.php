<?php

declare(strict_types=1);

class CategoryAdmin
{
    public function index(): void
    {
        admin_require('categories.edit');
        admin_view('categories', [
            'title' => 'Categories',
            'categories' => Category::all(),
            'editing' => isset($_GET['edit']) ? Category::find((int) $_GET['edit']) : null,
        ]);
    }

    public function save(): void
    {
        admin_require('categories.edit');
        Csrf::verify();
        $id = (int) ($_POST['id'] ?? 0);
        $validator = Validator::make($_POST, [
            'name' => 'required|max:120',
            'description' => 'max:1000',
            'status' => 'required|in:active,inactive',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/admin/categories');
        }
        $slug = unique_slug('categories', slugify((string) ($_POST['slug'] ?: $_POST['name'])), $id ?: null);
        $data = [
            'name' => trim((string) $_POST['name']),
            'slug' => $slug,
            'description' => trim((string) ($_POST['description'] ?? '')),
            'meta_title' => trim((string) ($_POST['meta_title'] ?? '')) ?: null,
            'meta_description' => trim((string) ($_POST['meta_description'] ?? '')) ?: null,
            'status' => (string) $_POST['status'],
        ];
        if ($id && Category::find($id)) {
            Category::update($id, $data);
            ActivityLog::record('category.update', 'Updated category ' . $data['name']);
        } else {
            Category::create($data);
            ActivityLog::record('category.create', 'Created category ' . $data['name']);
        }
        flash('success', 'Category saved.');
        redirect('/admin/categories');
    }

    public function delete(array $params): void
    {
        admin_require('categories.edit');
        Csrf::verify();
        try {
            Category::delete((int) $params['id']);
            flash('success', 'Category deleted.');
        } catch (Throwable) {
            flash('error', 'Move or delete the posts in this category first.');
        }
        redirect('/admin/categories');
    }
}

class TagAdmin
{
    public function index(): void
    {
        admin_require('tags.edit');
        admin_view('tags', [
            'title' => 'Tags',
            'tags' => Tag::all(),
            'editing' => isset($_GET['edit']) ? Tag::find((int) $_GET['edit']) : null,
        ]);
    }

    public function save(): void
    {
        admin_require('tags.edit');
        Csrf::verify();
        $id = (int) ($_POST['id'] ?? 0);
        $validator = Validator::make($_POST, ['name' => 'required|max:80']);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/admin/tags');
        }
        $slug = unique_slug('tags', slugify((string) ($_POST['slug'] ?: $_POST['name'])), $id ?: null);
        $data = ['name' => trim((string) $_POST['name']), 'slug' => $slug];
        if ($id && Tag::find($id)) {
            Tag::update($id, $data);
        } else {
            Tag::create($data);
        }
        ActivityLog::record('tag.save', 'Saved tag ' . $data['name']);
        flash('success', 'Tag saved.');
        redirect('/admin/tags');
    }

    public function delete(array $params): void
    {
        admin_require('tags.edit');
        Csrf::verify();
        Tag::delete((int) $params['id']);
        flash('success', 'Tag deleted.');
        redirect('/admin/tags');
    }
}
