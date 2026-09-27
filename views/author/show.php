<section class="author-hero">
    <div class="container author-hero__inner">
        <?php if (!empty($author['avatar'])): ?>
            <img src="<?= e(media_src($author['avatar'])) ?>" alt="" width="120" height="120">
        <?php else: ?>
            <span class="avatar-fallback avatar-fallback--xl"><?= e(initials($author['name'])) ?></span>
        <?php endif; ?>
        <div>
            <p class="kicker"><?= e($author['role_name']) ?></p>
            <h1><?= e($author['name']) ?></h1>
            <p class="lede"><?= e($author['bio'] ?: 'Writes for ' . site_name() . '.') ?></p>
            <p class="muted"><?= (int) $totalArticles ?> published stor<?= $totalArticles === 1 ? 'y' : 'ies' ?></p>
            <div class="social-row">
                <?php if (!empty($author['website']) && safe_url((string) $author['website'])): ?>
                    <a href="<?= e($author['website']) ?>" rel="noopener noreferrer" target="_blank">Website</a>
                <?php endif; ?>
                <?php if (!empty($author['twitter'])): ?>
                    <a href="https://x.com/<?= e(ltrim((string) $author['twitter'], '@')) ?>" rel="noopener noreferrer" target="_blank">X</a>
                <?php endif; ?>
                <?php if (!empty($author['linkedin']) && safe_url((string) $author['linkedin'])): ?>
                    <a href="<?= e($author['linkedin']) ?>" rel="noopener noreferrer" target="_blank">LinkedIn</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if (!$posts): ?>
            <div class="empty-state"><h2>No published stories yet.</h2></div>
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
