<div class="toolbar"><a class="btn" href="/admin/newsletter/export">Export CSV</a></div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Email</th><th>Status</th><th>Joined</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($subscribers as $subscriber): ?>
            <tr>
                <td><?= e($subscriber['email']) ?></td>
                <td><span class="badge"><?= e($subscriber['status']) ?></span></td>
                <td><?= e(format_date($subscriber['created_at'])) ?></td>
                <td class="row-actions">
                    <form method="post" action="/admin/newsletter/status/<?= (int) $subscriber['id'] ?>">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="status" value="<?= $subscriber['status'] === 'active' ? 'unsubscribed' : 'active' ?>">
                        <button><?= $subscriber['status'] === 'active' ? 'Unsubscribe' : 'Activate' ?></button>
                    </form>
                    <form method="post" action="/admin/newsletter/delete/<?= (int) $subscriber['id'] ?>" data-confirm="Remove this subscriber?"><?= Csrf::field() ?><button>Delete</button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
