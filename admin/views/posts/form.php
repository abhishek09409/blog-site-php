<?php
$post = $post ?? null;
$action = $post ? '/admin/posts/edit/' . (int) $post['id'] : '/admin/posts/create';
$selectedTags = array_map('intval', array_column($post['tags'] ?? [], 'id'));
if (isset($_SESSION['_old']['tags']) && is_array($_SESSION['_old']['tags'])) {
    $selectedTags = array_map('intval', $_SESSION['_old']['tags']);
}
$value = static function (string $key, $fallback = '') use ($post) {
    $current = $post[$key] ?? $fallback;
    return old($key, $current ?? '');
};
?>
<form class="editor-layout" method="post" action="<?= e($action) ?>" enctype="multipart/form-data" id="post-form">
    <?= Csrf::field() ?>
    <div class="editor-main panel">
        <label>Title
            <input name="title" required maxlength="220" value="<?= e($value('title')) ?>" data-title>
            <?= field_error('title') ?>
        </label>
        <label>Slug
            <input name="slug" value="<?= e($value('slug')) ?>" data-slug placeholder="generated from the title">
        </label>
        <label>Excerpt
            <textarea name="excerpt" rows="3" maxlength="500"><?= e($value('excerpt')) ?></textarea>
        </label>
        <div class="editor-toolbar" data-editor-toolbar>
            <button type="button" data-cmd="bold"><b>B</b></button>
            <button type="button" data-cmd="italic"><i>I</i></button>
            <button type="button" data-cmd="h2">H2</button>
            <button type="button" data-cmd="h3">H3</button>
            <button type="button" data-cmd="link">Link</button>
            <button type="button" data-cmd="ul">List</button>
            <button type="button" data-cmd="ol">Numbers</button>
            <button type="button" data-cmd="quote">Quote</button>
            <button type="button" data-cmd="code">Code</button>
            <button type="button" data-cmd="image">Image</button>
            <button type="button" data-cmd="table">Table</button>
            <button type="button" data-cmd="youtube">YouTube</button>
        </div>
        <?php $editorHtml = Sanitizer::html((string) $value('content', $post['content'] ?? '')); ?>
        <div class="editor-canvas prose" contenteditable="true" data-editor><?= $editorHtml ?></div>
        <textarea name="content" hidden data-content><?= e($editorHtml) ?></textarea>
        <?= field_error('content') ?>
    </div>
    <aside class="editor-side">
        <section class="panel">
            <h2>Publish</h2>
            <label>Status
                <select name="status">
                    <?php foreach (['draft', 'published', 'scheduled', 'unpublished'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= selected($value('status', 'draft'), $status) ?>><?= e(ucfirst($status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Visibility
                <select name="visibility">
                    <option value="public" <?= selected($value('visibility', 'public'), 'public') ?>>Public</option>
                    <option value="private" <?= selected($value('visibility'), 'private') ?>>Private</option>
                </select>
            </label>
            <label>Publish date
                <input type="datetime-local" name="published_at" value="<?= e($value('published_at') ? date('Y-m-d\TH:i', strtotime((string) $value('published_at'))) : '') ?>">
            </label>
            <label class="check"><input type="checkbox" name="is_featured" value="1" <?= checked((string) $value('is_featured') === '1') ?>> Featured</label>
            <label class="check"><input type="checkbox" name="is_trending" value="1" <?= checked((string) $value('is_trending') === '1') ?>> Trending</label>
            <?php if ($canAssign): ?>
                <label>Author
                    <select name="author_id">
                        <?php foreach ($authors as $author): ?>
                            <option value="<?= (int) $author['id'] ?>" <?= selected($value('author_id', Auth::id()), $author['id']) ?>><?= e($author['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>
            <button class="btn" type="submit">Save</button>
            <?php if ($post): ?><a href="/preview/<?= (int) $post['id'] ?>" target="_blank" rel="noopener">Preview</a><?php endif; ?>
        </section>
        <section class="panel">
            <h2>Category</h2>
            <select name="category_id" required>
                <option value="">Choose</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= selected($value('category_id'), $category['id']) ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?= field_error('category_id') ?>
            <h2>Tags</h2>
            <div class="tag-picks">
                <?php foreach ($tags as $tag): ?>
                    <label><input type="checkbox" name="tags[]" value="<?= (int) $tag['id'] ?>" <?= in_array((int) $tag['id'], $selectedTags, true) ? 'checked' : '' ?>> <?= e($tag['name']) ?></label>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="panel">
            <h2>Featured image</h2>
            <input type="hidden" name="featured_image" value="<?= e($value('featured_image')) ?>" data-featured>
            <img class="featured-preview" alt="" data-featured-preview src="<?= e(media_src((string) $value('featured_image'))) ?>" <?= $value('featured_image') ? '' : 'hidden' ?>>
            <label>Upload<input type="file" name="featured_upload" accept="image/jpeg,image/png,image/gif,image/webp" data-file-preview></label>
            <label>Alt text<input name="featured_image_alt" value="<?= e($value('featured_image_alt')) ?>"></label>
            <button type="button" class="btn btn-quiet" data-open-media>Choose from library</button>
            <?= field_error('featured_image') ?>
        </section>
        <section class="panel">
            <h2>SEO</h2>
            <label>SEO title<input name="meta_title" maxlength="180" value="<?= e($value('meta_title')) ?>"></label>
            <label>Meta description<textarea name="meta_description" maxlength="300" rows="3"><?= e($value('meta_description')) ?></textarea></label>
            <label>Canonical URL<input name="canonical_url" value="<?= e($value('canonical_url')) ?>" placeholder="https://"><?= field_error('canonical_url') ?></label>
        </section>
    </aside>
</form>
<div class="modal" id="media-modal" hidden>
    <div class="modal__card modal__card--wide">
        <h2>Media library</h2>
        <div class="media-grid">
            <?php foreach ($media as $item): ?>
                <button type="button" class="media-pick" data-src="<?= e($item['filepath']) ?>" data-alt="<?= e((string) $item['alt_text']) ?>">
                    <img src="<?= e(media_src($item['filepath'])) ?>" alt="">
                </button>
            <?php endforeach; ?>
        </div>
        <button type="button" data-close-media>Close</button>
    </div>
</div>
<script src="<?= e(asset('assets/js/editor.js')) ?>"></script>
