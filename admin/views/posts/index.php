<div class="toolbar">
    <form method="get" class="filters">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search titles">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach (['draft', 'published', 'scheduled', 'unpublished'] as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($status, $option) ?>><?= e(ucfirst($option)) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Filter</button>
    </form>
    <a class="btn" href="/admin/posts/create">Add new post</a>
</div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Status</th><th>Views</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($result['items'] as $post): ?>
            <tr>
                <td><a href="/admin/posts/edit/<?= (int) $post['id'] ?>"><?= e($post['title']) ?></a></td>
                <td><?= e($post['author_name']) ?></td>
                <td><?= e($post['category_name']) ?></td>
                <td><span class="badge"><?= e($post['status']) ?></span></td>
                <td><?= number_format((int) $post['views']) ?></td>
                <td class="row-actions">
                    <a href="/preview/<?= (int) $post['id'] ?>">Preview</a>
                    <form method="post" action="/admin/posts/duplicate/<?= (int) $post['id'] ?>"><?= Csrf::field() ?><button type="submit">Duplicate</button></form>
                    <form method="post" action="/admin/posts/delete/<?= (int) $post['id'] ?>" data-confirm="Delete this post?"><?= Csrf::field() ?><button type="submit">Delete</button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$result['items']): ?><tr><td colspan="6">No posts match this filter.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php if ($result['pages'] > 1): ?>
    <nav class="pager">
        <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
            <a class="<?= $i === $result['page'] ? 'is-current' : '' ?>" href="/admin/posts?page=<?= $i ?>&status=<?= e($status) ?>&q=<?= e($q) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
