<section class="page-hero">
    <div class="container">
        <p class="kicker">Section</p>
        <h1><?= e($category['name']) ?></h1>
        <?php if (!empty($category['description'])): ?>
            <p class="lede"><?= e($category['description']) ?></p>
        <?php endif; ?>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if (!$posts): ?>
            <div class="empty-state"><h2>No stories in this section yet.</h2></div>
        <?php else: ?>
            <?php $feature = $posts[0]; $rest = array_slice($posts, 1); ?>
            <?php partial('post-card', ['post' => $feature, 'variant' => 'feature']); ?>
            <?php if ($rest): ?>
                <div class="card-grid">
                    <?php foreach ($rest as $post): ?>
                        <?php partial('post-card', ['post' => $post]); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php partial('pagination', ['pagination' => $pagination, 'base' => $base, 'query' => $query]); ?>
        <?php endif; ?>
    </div>
</section>
