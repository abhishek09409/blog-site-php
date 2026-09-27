<div class="split">
    <form class="panel stack" method="post" action="/admin/tags">
        <?= Csrf::field() ?>
        <h2><?= $editing ? 'Edit tag' : 'New tag' ?></h2>
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
        <label>Name<input name="name" required value="<?= e(old('name', $editing['name'] ?? '')) ?>"><?= field_error('name') ?></label>
        <label>Slug<input name="slug" value="<?= e(old('slug', $editing['slug'] ?? '')) ?>"></label>
        <button class="btn" type="submit">Save tag</button>
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tag</th><th>Posts</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($tags as $tag): ?>
                <tr>
                    <td>#<?= e($tag['name']) ?></td>
                    <td><?= (int) $tag['post_count'] ?></td>
                    <td class="row-actions">
                        <a href="/admin/tags?edit=<?= (int) $tag['id'] ?>">Edit</a>
                        <form method="post" action="/admin/tags/delete/<?= (int) $tag['id'] ?>" data-confirm="Delete this tag?"><?= Csrf::field() ?><button>Delete</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
