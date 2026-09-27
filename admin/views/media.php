<form class="panel toolbar" method="post" action="/admin/media" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple required>
    <input name="alt_text" placeholder="Alt text for this upload">
    <button class="btn" type="submit">Upload</button>
</form>
<form class="filters" method="get">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search images">
    <button class="btn btn-quiet" type="submit">Search</button>
</form>
<div class="media-grid">
    <?php foreach ($items as $item): ?>
        <article class="media-card">
            <img src="<?= e(media_src($item['filepath'])) ?>" alt="<?= e((string) $item['alt_text']) ?>">
            <form method="post" action="/admin/media/alt/<?= (int) $item['id'] ?>">
                <?= Csrf::field() ?>
                <input name="alt_text" value="<?= e((string) $item['alt_text']) ?>" placeholder="Alt text">
                <button type="submit">Save alt</button>
            </form>
            <form method="post" action="/admin/media/delete/<?= (int) $item['id'] ?>" data-confirm="Delete this image?"><?= Csrf::field() ?><button>Delete</button></form>
        </article>
    <?php endforeach; ?>
</div>
<?php if (!$items): ?><p class="empty">No images yet.</p><?php endif; ?>
