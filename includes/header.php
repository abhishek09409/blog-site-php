<?php
/** @var array $seo */
/** @var array $headerCategories */
/** @var array $headerMenu */
/** @var array|null $currentUser */
$success = flash('success');
$error = flash('error');
$logo = setting('logo', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seo['title']) ?></title>
    <meta name="description" content="<?= e($seo['description']) ?>">
    <meta name="robots" content="<?= e($seo['robots']) ?>">
    <link rel="canonical" href="<?= e($seo['canonical']) ?>">
    <meta property="og:site_name" content="<?= e(site_name()) ?>">
    <meta property="og:title" content="<?= e($seo['title']) ?>">
    <meta property="og:description" content="<?= e($seo['description']) ?>">
    <meta property="og:url" content="<?= e($seo['canonical']) ?>">
    <meta property="og:type" content="<?= e($seo['type']) ?>">
    <meta property="og:image" content="<?= e($seo['image']) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($seo['title']) ?>">
    <meta name="twitter:description" content="<?= e($seo['description']) ?>">
    <meta name="twitter:image" content="<?= e($seo['image']) ?>">
    <?php if ($verify = setting('gsc_verification', '')): ?>
        <meta name="google-site-verification" content="<?= e($verify) ?>">
    <?php endif; ?>
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <meta name="app-base" content="<?= e(app_base()) ?>">
    <link rel="icon" href="<?= e(media_src(setting('favicon', 'assets/images/favicon.svg'))) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <style>:root{--primary: <?= e(primary_color()) ?>;}</style>
    <?php foreach ($seo['jsonld'] as $block): ?>
        <?= json_ld_script($block) ?>
    <?php endforeach; ?>
    <?php if ($ga = setting('ga_id', '')): ?>
        <?php if (preg_match('/^(G|UA|GT)-[A-Z0-9-]+$/', $ga)): ?>
            <meta name="ga-id" content="<?= e($ga) ?>">
            <script src="<?= e(asset('assets/js/analytics.js')) ?>" defer></script>
        <?php endif; ?>
    <?php endif; ?>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <div class="utility">
        <div class="container utility__row">
            <p><?= e(date('l, F j')) ?></p>
            <p class="utility__issue"><?= e(setting('site_description', 'An independent publication')) ?></p>
            <a href="/#newsletter">Newsletter</a>
        </div>
    </div>
    <div class="container header-main">
        <button class="icon-btn nav-toggle" type="button" aria-expanded="false" aria-controls="mobile-nav" data-nav-toggle>
            <?= icon('menu') ?>
            <span class="sr-only">Open menu</span>
        </button>
        <a class="logo" href="/">
            <?php if ($logo): ?>
                <img src="<?= e(media_src($logo)) ?>" alt="<?= e(site_name()) ?>">
            <?php else: ?>
                <img src="/assets/images/logo-mark.svg" alt="" width="36" height="36">
            <?php endif; ?>
            <span><?= e(site_name()) ?></span>
        </a>
        <nav class="primary-nav" aria-label="Primary">
            <?php if ($headerMenu): ?>
                <?php foreach ($headerMenu as $item): ?>
                    <a class="<?= e(nav_active((string) $item['url'])) ?>" href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a>
                <?php endforeach; ?>
            <?php else: ?>
                <a class="<?= e(nav_active('/blog')) ?>" href="/blog">Journal</a>
                <a class="<?= e(nav_active('/about')) ?>" href="/about">About</a>
                <a class="<?= e(nav_active('/contact')) ?>" href="/contact">Contact</a>
            <?php endif; ?>
        </nav>
        <div class="header-actions">
            <button class="icon-btn" type="button" data-search-open aria-label="Search">
                <?= icon('search') ?>
            </button>
            <?php if ($currentUser): ?>
                <?php if (Auth::can('dashboard')): ?>
                    <a class="btn btn-primary" href="/admin">Newsroom</a>
                <?php else: ?>
                    <a class="btn btn-primary" href="/account">Account</a>
                <?php endif; ?>
            <?php else: ?>
                <a class="btn btn-ghost" href="/login">Sign in</a>
                <a class="btn btn-primary" href="/register">Join</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="category-bar">
        <div class="container category-bar__row">
            <?php foreach ($headerCategories as $category): ?>
                <a href="/category/<?= e($category['slug']) ?>"><?= e($category['name']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</header>

<div class="nav-drawer" id="mobile-nav" hidden>
    <div class="nav-drawer__panel">
        <div class="nav-drawer__top">
            <strong><?= e(site_name()) ?></strong>
            <button class="icon-btn" type="button" data-nav-close aria-label="Close menu"><?= icon('close') ?></button>
        </div>
        <form class="drawer-search" action="/search" method="get">
            <label class="sr-only" for="drawer-q">Search</label>
            <input id="drawer-q" type="search" name="q" placeholder="Search the journal">
        </form>
        <nav>
            <a href="/">Home</a>
            <a href="/blog">Journal</a>
            <?php foreach ($headerCategories as $category): ?>
                <a href="/category/<?= e($category['slug']) ?>"><?= e($category['name']) ?></a>
            <?php endforeach; ?>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
            <?php if ($currentUser && Auth::can('dashboard')): ?>
                <a href="/admin">Newsroom</a>
            <?php elseif ($currentUser): ?>
                <a href="/account">Account</a>
            <?php else: ?>
                <a href="/login">Sign in</a>
                <a href="/register">Create account</a>
            <?php endif; ?>
        </nav>
    </div>
</div>

<div class="search-modal" id="search-modal" hidden>
    <div class="search-modal__backdrop" data-search-close></div>
    <div class="search-modal__panel" role="dialog" aria-modal="true" aria-label="Search">
        <form action="/search" method="get">
            <label class="sr-only" for="site-search">Search</label>
            <input id="site-search" type="search" name="q" placeholder="Search stories, topics, authors" autocomplete="off" data-live-search>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
        <div class="search-suggest" data-search-suggest></div>
    </div>
</div>

<?php if ($success || $error): ?>
    <div class="flash-stack" data-flash data-type="<?= e($error ? 'error' : 'success') ?>" data-message="<?= e($error ?: $success) ?>"></div>
<?php endif; ?>
<main id="main">
