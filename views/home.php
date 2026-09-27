<?php if (!$hero): ?>
    <section class="container empty-state">
        <h1>The journal is being set.</h1>
        <p>Import the database schema and seed file to publish the first issue.</p>
    </section>
<?php else: ?>
<section class="hero-wrap">
    <div class="container hero">
        <?php partial('post-card', ['post' => $hero, 'variant' => 'hero']); ?>
        <div class="hero__side">
            <?php foreach ($secondary as $post): ?>
                <?php partial('post-card', ['post' => $post, 'variant' => 'side']); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($trending)): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Trending</h2>
            <a href="/blog?sort=trending">View the list <?= icon('arrow', 16) ?></a>
        </div>
        <div class="trending-grid">
            <?php foreach ($trending as $index => $post): ?>
                <a class="trend" href="/blog/<?= e($post['slug']) ?>">
                    <span>0<?= $index + 1 ?></span>
                    <div>
                        <small><?= e($post['category_name']) ?></small>
                        <strong><?= e($post['title']) ?></strong>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($latest)): ?>
<section class="section section--paper">
    <div class="container">
        <div class="section-head">
            <h2>Latest</h2>
            <a href="/blog">All stories <?= icon('arrow', 16) ?></a>
        </div>
        <div class="card-grid">
            <?php foreach ($latest as $post): ?>
                <?php partial('post-card', ['post' => $post, 'variant' => 'card']); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php foreach ($sections as $section): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <h2><?= e($section['name']) ?></h2>
                <?php if (!empty($section['description'])): ?>
                    <p><?= e(excerpt_text((string) $section['description'], 140)) ?></p>
                <?php endif; ?>
            </div>
            <a href="/category/<?= e($section['slug']) ?>">More in <?= e($section['name']) ?> <?= icon('arrow', 16) ?></a>
        </div>
        <div class="card-grid card-grid--3">
            <?php foreach ($section['posts'] as $post): ?>
                <?php partial('post-card', ['post' => $post, 'variant' => 'card']); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

<section class="newsletter-band" id="newsletter">
    <div class="container newsletter-band__inner">
        <div>
            <p class="kicker">The briefing</p>
            <h2>Read Meridian before everyone else does.</h2>
            <p>A short letter with the stories worth your time. No recycled roundups.</p>
        </div>
        <?php partial('newsletter'); ?>
    </div>
</section>
