<?php
/** @var array $pagination */
/** @var string $base */
/** @var array $query */
$query = $query ?? ($queryParams ?? []);
if (($pagination['pages'] ?? 1) <= 1) {
    return;
}
?>
<nav class="pagination" aria-label="Pagination">
    <?php if ($pagination['page'] > 1): ?>
        <a href="<?= e(page_url($base, $pagination['page'] - 1, $query)) ?>">Previous</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
        <?php if ($i === 1 || $i === $pagination['pages'] || abs($i - $pagination['page']) <= 1): ?>
            <a class="<?= $i === $pagination['page'] ? 'is-current' : '' ?>" href="<?= e(page_url($base, $i, $query)) ?>"><?= $i ?></a>
        <?php elseif (abs($i - $pagination['page']) === 2): ?>
            <span>…</span>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($pagination['page'] < $pagination['pages']): ?>
        <a href="<?= e(page_url($base, $pagination['page'] + 1, $query)) ?>">Next</a>
    <?php endif; ?>
</nav>
