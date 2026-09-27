<?php $max = max(1, ...array_column($series, 'count')); ?>
<section class="stat-grid">
    <?php
    $stats = [
        ['Posts', $counts['total']],
        ['Published', $counts['published']],
        ['Drafts', $counts['drafts']],
        ['Views', number_format($counts['views'])],
        ['Comments', $comments],
        ['Users', $users],
        ['Subscribers', $subscribers],
        ['Scheduled', $counts['scheduled']],
    ];
    foreach ($stats as [$label, $value]): ?>
        <article class="stat"><span><?= e($label) ?></span><strong><?= e((string) $value) ?></strong></article>
    <?php endforeach; ?>
</section>
<section class="panel">
    <h2>Views, last 14 days</h2>
    <div class="chart" aria-label="Views chart">
        <?php foreach ($series as $point): ?>
            <div class="chart__col" title="<?= e($point['label'] . ': ' . $point['count']) ?>">
                <span style="height: <?= max(4, (int) round(($point['count'] / $max) * 120)) ?>px"></span>
                <small><?= e($point['label']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<div class="split">
    <section class="panel">
        <h2>Popular posts</h2>
        <ul class="plain">
            <?php foreach ($popular as $post): ?>
                <li><a href="/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a><span><?= number_format((int) $post['views']) ?></span></li>
            <?php endforeach; ?>
        </ul>
        <h2>Most viewed sections</h2>
        <ul class="plain">
            <?php foreach ($categories as $category): ?>
                <li><a href="/category/<?= e($category['slug']) ?>"><?= e($category['name']) ?></a><span><?= number_format((int) $category['views']) ?></span></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="panel">
        <h2>Recent posts</h2>
        <ul class="plain">
            <?php foreach ($recentPosts as $post): ?>
                <li><a href="/admin/posts/edit/<?= (int) $post['id'] ?>"><?= e($post['title']) ?></a><span class="badge"><?= e($post['status']) ?></span></li>
            <?php endforeach; ?>
        </ul>
        <h2>Recent comments</h2>
        <ul class="plain">
            <?php foreach ($recentComments as $comment): ?>
                <li><span><?= e(excerpt_text($comment['content'], 80)) ?></span><span class="badge"><?= e($comment['status']) ?></span></li>
            <?php endforeach; ?>
        </ul>
        <h2>Activity</h2>
        <ul class="plain">
            <?php foreach ($activity as $row): ?>
                <li><span><?= e($row['description']) ?></span><small><?= e(format_date($row['created_at'], 'M j, H:i')) ?></small></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
