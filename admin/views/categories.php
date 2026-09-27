<div class="split">
    <form class="panel stack" method="post" action="/admin/categories">
        <?= Csrf::field() ?>
        <h2><?= $editing ? 'Edit category' : 'New category' ?></h2>
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
        <label>Name<input name="name" required value="<?= e(old('name', $editing['name'] ?? '')) ?>"><?= field_error('name') ?></label>
        <label>Slug<input name="slug" value="<?= e(old('slug', $editing['slug'] ?? '')) ?>"></label>
        <label>Description<textarea name="description" rows="4"><?= e(old('description', $editing['description'] ?? '')) ?></textarea></label>
        <label>SEO title<input name="meta_title" value="<?= e(old('meta_title', $editing['meta_title'] ?? '')) ?>"></label>
        <label>Meta description<textarea name="meta_description" rows="3"><?= e(old('meta_description', $editing['meta_description'] ?? '')) ?></textarea></label>
        <label>Status
            <select name="status">
                <option value="active" <?= selected(old('status', $editing['status'] ?? 'active'), 'active') ?>>Active</option>
                <option value="inactive" <?= selected(old('status', $editing['status'] ?? ''), 'inactive') ?>>Inactive</option>
            </select>
        </label>
        <button class="btn" type="submit">Save category</button>
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Posts</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= e($category['name']) ?><br><small>/category/<?= e($category['slug']) ?></small></td>
                    <td><?= (int) $category['post_count'] ?></td>
                    <td><span class="badge"><?= e($category['status']) ?></span></td>
                    <td class="row-actions">
                        <a href="/admin/categories?edit=<?= (int) $category['id'] ?>">Edit</a>
                        <form method="post" action="/admin/categories/delete/<?= (int) $category['id'] ?>" data-confirm="Delete this category?"><?= Csrf::field() ?><button>Delete</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
