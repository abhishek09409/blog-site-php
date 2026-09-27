<div class="toolbar"><a class="btn" href="/admin/users/create">Add user</a></div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Posts</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= e($user['name']) ?><br><small>@<?= e($user['username']) ?></small></td>
                <td><?= e($user['email']) ?></td>
                <td><?= e($user['role_name']) ?></td>
                <td><span class="badge"><?= e($user['status']) ?></span></td>
                <td><?= (int) $user['post_count'] ?></td>
                <td class="row-actions">
                    <a href="/admin/users/edit/<?= (int) $user['id'] ?>">Edit</a>
                    <form method="post" action="/admin/users/delete/<?= (int) $user['id'] ?>" data-confirm="Delete this user?"><?= Csrf::field() ?><button>Delete</button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
