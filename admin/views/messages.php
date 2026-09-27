<div class="split">
    <div class="table-wrap">
        <table>
            <thead><tr><th>From</th><th>Subject</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= e($message['name']) ?><br><small><?= e($message['email']) ?></small></td>
                    <td><?= e($message['subject']) ?> <?php if ($message['status'] === 'unread'): ?><span class="badge">unread</span><?php endif; ?></td>
                    <td><a href="/admin/messages/read/<?= (int) $message['id'] ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <article class="panel">
        <?php if ($open): ?>
            <h2><?= e($open['subject']) ?></h2>
            <p><?= e($open['name']) ?> · <?= e($open['email']) ?></p>
            <p><?= nl2br(e($open['message'])) ?></p>
            <form method="post" action="/admin/messages/delete/<?= (int) $open['id'] ?>" data-confirm="Delete this message?"><?= Csrf::field() ?><button>Delete</button></form>
        <?php else: ?>
            <p>Select a message to read it.</p>
        <?php endif; ?>
    </article>
</div>
