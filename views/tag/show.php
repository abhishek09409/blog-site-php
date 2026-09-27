<section class="page-hero">
    <div class="container">
        <p class="kicker">Tag</p>
        <h1>#<?= e($tag['name']) ?></h1>
        <p class="lede"><?= (int) $pagination['total'] ?> stor<?= $pagination['total'] === 1 ? 'y' : 'ies' ?> filed under this tag.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if (!$posts): ?>
            <div class="empty-state"><h2>No stories with this tag yet.</h2></div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($posts as $post): ?>
                    <?php partial('post-card', ['post' => $post]); ?>
                <?php endforeach; ?>
            </div>
            <?php partial('pagination', ['pagination' => $pagination, 'base' => $base, 'query' => $query]); ?>
        <?php endif; ?>
    </div>
</section>
