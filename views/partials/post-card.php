<?php
/** @var array $post */
/** @var string $variant */
$variant = $variant ?? 'card';
$href = '/blog/' . $post['slug'];
$image = media_src($post['featured_image'] ?? '');
$alt = $post['featured_image_alt'] ?: $post['title'];
$eager = in_array($variant, ['hero', 'side', 'feature'], true);
?>
<article class="story story--<?= e($variant) ?>">
    <a class="story__media" href="<?= e($href) ?>">
        <?php if ($image): ?>
            <img src="<?= e($image) ?>" alt="<?= e($alt) ?>" width="960" height="600" loading="<?= $eager ? 'eager' : 'lazy' ?>"<?= $variant === 'hero' ? ' fetchpriority="high"' : '' ?>>
        <?php else: ?>
            <span class="story__fallback"><?= e($post['category_name'] ?? 'Story') ?></span>
        <?php endif; ?>
    </a>
    <div class="story__body">
        <?php if (!empty($post['category_slug'])): ?>
            <a class="kicker" href="/category/<?= e($post['category_slug']) ?>"><?= e($post['category_name']) ?></a>
        <?php endif; ?>
        <h3><a href="<?= e($href) ?>"><?= e($post['title']) ?></a></h3>
        <?php if ($variant !== 'mini' && !empty($post['excerpt'])): ?>
            <p><?= e(excerpt_text((string) $post['excerpt'], $variant === 'hero' ? 220 : 140)) ?></p>
        <?php endif; ?>
        <div class="story__meta">
            <a href="/author/<?= e($post['author_username']) ?>"><?= e($post['author_name']) ?></a>
            <span><?= e(format_date((string) ($post['published_at'] ?? ''))) ?></span>
            <span><?= (int) $post['reading_time'] ?> min read</span>
            <span><?= e(number_short((int) $post['views'])) ?> views</span>
        </div>
    </div>
</article>
