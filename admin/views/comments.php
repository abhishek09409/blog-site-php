<div class="chip-row">
    <?php foreach (['pending', 'approved', 'rejected', 'spam', 'all'] as $filter): ?>
        <a class="<?= $status === $filter ? 'is-current' : '' ?>" href="/admin/comments?status=<?= e($filter) ?>"><?= e(ucfirst($filter)) ?></a>
    <?php endforeach; ?>
</div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Comment</th><th>On</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($comments as $comment): ?>
            <tr>
                <td><strong><?= e($comment['name']) ?></strong><br><?= e(excerpt_text($comment['content'], 180)) ?></td>
                <td><a href="/blog/<?= e($comment['post_slug']) ?>"><?= e($comment['post_title']) ?></a></td>
                <td><span class="badge"><?= e($comment['status']) ?></span></td>
                <td class="row-actions">
                    <?php if ($canModerate): ?>
                        <?php foreach (['approved' => 'Approve', 'rejected' => 'Reject', 'spam' => 'Spam'] as $value => $label): ?>
                            <form method="post" action="/admin/comments/status/<?= (int) $comment['id'] ?>"><?= Csrf::field() ?><input type="hidden" name="status" value="<?= e($value) ?>"><button><?= e($label) ?></button></form>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <form method="post" action="/admin/comments/delete/<?= (int) $comment['id'] ?>" data-confirm="Delete this comment?"><?= Csrf::field() ?><button>Delete</button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$comments): ?><tr><td colspan="4">No comments in this queue.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
