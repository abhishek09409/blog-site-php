<?php
/** @var string $content */
/** @var string $title */
/** @var array $adminUser */
$path = current_path();
$links = [
    ['Dashboard', '/admin', 'dashboard', Auth::can('dashboard')],
    ['Posts', '/admin/posts', 'posts.edit', Auth::canAny(['posts.edit', 'posts.own'])],
    ['Add new post', '/admin/posts/create', 'posts.edit', Auth::canAny(['posts.edit', 'posts.own'])],
    ['Categories', '/admin/categories', 'categories.edit', Auth::can('categories.edit')],
    ['Tags', '/admin/tags', 'tags.edit', Auth::can('tags.edit')],
    ['Media library', '/admin/media', 'media.view', Auth::canAny(['media.view', 'media.own'])],
    ['Comments', '/admin/comments', 'comments.moderate', Auth::canAny(['comments.moderate', 'comments.own'])],
    ['Users', '/admin/users', 'users.manage', Auth::can('users.manage')],
    ['Newsletter', '/admin/newsletter', 'newsletter.view', Auth::can('newsletter.view')],
    ['Messages', '/admin/messages', 'messages.view', Auth::can('messages.view')],
    ['Menus', '/admin/menus', 'menus.edit', Auth::can('menus.edit')],
    ['Settings', '/admin/settings', 'settings.edit', Auth::can('settings.edit')],
    ['Profile', '/admin/profile', 'profile', true],
];
$success = flash('success');
$error = flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> — Newsroom</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <meta name="app-base" content="<?= e(app_base()) ?>">
    <link rel="icon" href="<?= e(media_src(setting('favicon', 'assets/images/favicon.svg'))) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@360;450;560;650&family=Newsreader:opsz,wght@6..72,500;6..72,650&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
    <style>:root{--primary: <?= e(primary_color()) ?>}</style>
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="/admin"><img src="/assets/images/logo-mark.svg" alt="" width="32" height="32"><span>Meridian</span></a>
        <nav>
            <?php foreach ($links as [$label, $href, $perm, $show]): ?>
                <?php if (!$show) continue; ?>
                <a class="<?= $path === $href || ($href !== '/admin' && str_starts_with($path, $href)) ? 'is-active' : '' ?>" href="<?= e($href) ?>">
                    <?= e($label) ?>
                    <?php if ($href === '/admin/comments' && !empty($pendingComments)): ?><em><?= (int) $pendingComments ?></em><?php endif; ?>
                    <?php if ($href === '/admin/messages' && !empty($unreadMessages)): ?><em><?= (int) $unreadMessages ?></em><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <form method="post" action="/logout"><?= Csrf::field() ?><button type="submit">Sign out</button></form>
    </aside>
    <div class="admin-main">
        <header class="admin-top">
            <button type="button" class="menu-btn" data-admin-nav aria-label="Open menu">Menu</button>
            <h1><?= e($title) ?></h1>
            <a href="/" target="_blank" rel="noopener">View site</a>
            <span class="who"><?= e($adminUser['name'] ?? '') ?></span>
        </header>
        <div class="admin-content">
            <?= $content ?>
        </div>
    </div>
</div>
<div class="modal" id="confirm-modal" hidden>
    <div class="modal__card">
        <h2>Confirm</h2>
        <p data-confirm-text>This cannot be undone.</p>
        <div class="modal__actions">
            <button type="button" data-confirm-cancel>Cancel</button>
            <button type="button" class="danger" data-confirm-ok>Delete</button>
        </div>
    </div>
</div>
<?php if ($success || $error): ?>
    <div data-flash data-type="<?= e($error ? 'error' : 'success') ?>" data-message="<?= e($error ?: $success) ?>" hidden></div>
<?php endif; ?>
<script src="<?= e(asset('assets/js/admin.js')) ?>"></script>
</body>
</html>
