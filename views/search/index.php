<section class="page-hero">
    <div class="container">
        <p class="kicker">Search</p>
        <h1><?= $query !== '' ? 'Results for “' . e($query) . '”' : 'Search the journal' ?></h1>
        <form class="search-page-form" action="/search" method="get">
            <label class="sr-only" for="q">Search</label>
            <input id="q" type="search" name="q" value="<?= e($query) ?>" placeholder="Try AI, startups, or PHP" required minlength="2">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
        <?php if ($query !== ''): ?>
            <p class="muted"><?= (int) $pagination['total'] ?> result<?= $pagination['total'] === 1 ? '' : 's' ?></p>
        <?php endif; ?>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if ($query !== '' && !$posts): ?>
            <div class="empty-state">
                <h2>No matches</h2>
                <p>Try a broader word, or browse the journal from the beginning.</p>
                <a class="btn btn-primary" href="/blog">Browse the journal</a>
            </div>
        <?php elseif ($posts): ?>
            <div class="card-grid">
                <?php foreach ($posts as $post): ?>
                    <?php partial('post-card', ['post' => $post]); ?>
                <?php endforeach; ?>
            </div>
            <?php partial('pagination', ['pagination' => $pagination, 'base' => $base, 'query' => $queryParams]); ?>
        <?php endif; ?>
    </div>
</section>
