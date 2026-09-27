<section class="page-hero">
    <div class="container">
        <p class="kicker">Meridian</p>
        <h1><?= e($heading) ?></h1>
        <p class="lede"><?= e($intro) ?></p>
        <form class="sort-bar" method="get" action="/blog">
            <label for="sort">Sort</label>
            <select id="sort" name="sort" data-auto-submit>
                <?php foreach (['latest' => 'Latest', 'popular' => 'Most read', 'trending' => 'Trending', 'oldest' => 'Oldest'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= selected($sort, $value) ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php if (!empty($tags)): ?>
            <div class="chip-row">
                <?php foreach ($tags as $tag): ?>
                    <a href="/tag/<?= e($tag['slug']) ?>">#<?= e($tag['name']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if (!$posts): ?>
            <div class="empty-state">
                <h2>Nothing in this issue yet.</h2>
                <p>Try another section or check back after the next publication.</p>
            </div>
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
