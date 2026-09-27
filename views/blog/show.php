<?php
$shareUrl = url('blog/' . $post['slug']);
$shareText = $post['title'];
?>
<article class="article">
    <?php if (!empty($preview)): ?>
        <div class="preview-banner">This is a preview. Readers cannot see this story yet. <a href="/admin/posts/edit/<?= (int) $post['id'] ?>">Edit</a></div>
    <?php endif; ?>
    <header class="article-hero">
        <div class="container article-hero__inner">
            <nav class="crumbs" aria-label="Breadcrumb">
                <a href="/">Home</a>
                <span>/</span>
                <a href="/category/<?= e($post['category_slug']) ?>"><?= e($post['category_name']) ?></a>
            </nav>
            <a class="kicker" href="/category/<?= e($post['category_slug']) ?>"><?= e($post['category_name']) ?></a>
            <h1><?= e($post['title']) ?></h1>
            <?php if (!empty($post['excerpt'])): ?>
                <p class="lede"><?= e($post['excerpt']) ?></p>
            <?php endif; ?>
            <div class="byline">
                <a class="byline__author" href="/author/<?= e($post['author_username']) ?>">
                    <?php if (!empty($post['author_avatar'])): ?>
                        <img src="<?= e(media_src($post['author_avatar'])) ?>" alt="" width="48" height="48">
                    <?php else: ?>
                        <span class="avatar-fallback"><?= e(initials($post['author_name'])) ?></span>
                    <?php endif; ?>
                    <span>
                        <strong><?= e($post['author_name']) ?></strong>
                        <small>Author</small>
                    </span>
                </a>
                <ul class="byline__facts">
                    <li><?= e(format_date((string) ($post['published_at'] ?: $post['created_at']))) ?></li>
                    <li><?= (int) $post['reading_time'] ?> min read</li>
                    <li data-view-count><?= e(number_short((int) $post['views'])) ?> views</li>
                </ul>
            </div>
        </div>
    </header>
    <?php if (!empty($post['featured_image'])): ?>
        <figure class="article-figure">
            <img src="<?= e(media_src($post['featured_image'])) ?>" alt="<?= e($post['featured_image_alt'] ?: $post['title']) ?>" width="1600" height="1000">
        </figure>
    <?php endif; ?>
    <div class="container article-layout">
        <div class="article-main">
            <div class="prose" data-article data-post-id="<?= (int) $post['id'] ?>">
                <?= $content ?>
            </div>
            <?php if (!empty($post['tags'])): ?>
                <div class="chip-row article-tags">
                    <?php foreach ($post['tags'] as $tag): ?>
                        <a href="/tag/<?= e($tag['slug']) ?>">#<?= e($tag['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="share-row">
                <span>Share</span>
                <a href="https://twitter.com/intent/tweet?url=<?= e(rawurlencode($shareUrl)) ?>&text=<?= e(rawurlencode($shareText)) ?>" target="_blank" rel="noopener noreferrer">X</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= e(rawurlencode($shareUrl)) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= e(rawurlencode($shareUrl)) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
                <button type="button" data-copy="<?= e($shareUrl) ?>">Copy link</button>
            </div>
            <aside class="author-box">
                <?php if (!empty($post['author_avatar'])): ?>
                    <img src="<?= e(media_src($post['author_avatar'])) ?>" alt="" width="72" height="72">
                <?php else: ?>
                    <span class="avatar-fallback avatar-fallback--lg"><?= e(initials($post['author_name'])) ?></span>
                <?php endif; ?>
                <div>
                    <p class="kicker">Written by</p>
                    <h2><a href="/author/<?= e($post['author_username']) ?>"><?= e($post['author_name']) ?></a></h2>
                    <p><?= e($post['author_bio'] ?: 'Contributor at ' . site_name()) ?></p>
                </div>
            </aside>
            <section class="comments" id="comments">
                <h2><?= (int) $commentCount ?> comment<?= $commentCount === 1 ? '' : 's' ?></h2>
                <?php if (!$comments): ?>
                    <p class="muted">Be the first to respond. Comments are reviewed before they appear.</p>
                <?php endif; ?>
                <div class="comment-list">
                    <?php
                    $renderComment = function (array $comment, bool $reply = false) use (&$renderComment): void {
                        ?>
                        <article class="comment<?= $reply ? ' is-reply' : '' ?>" id="comment-<?= (int) $comment['id'] ?>">
                            <header>
                                <strong><?= e($comment['name']) ?></strong>
                                <time datetime="<?= e($comment['created_at']) ?>"><?= e(format_date($comment['created_at'], 'M j, Y')) ?></time>
                            </header>
                            <p><?= nl2br(e($comment['content'])) ?></p>
                            <?php if (!$reply): ?>
                                <button type="button" class="text-btn" data-reply="<?= (int) $comment['id'] ?>" data-reply-name="<?= e($comment['name']) ?>">Reply</button>
                            <?php endif; ?>
                        </article>
                        <?php foreach ($comment['replies'] ?? [] as $child) {
                            $renderComment($child, true);
                        }
                    };
                    foreach ($comments as $comment) {
                        $renderComment($comment, false);
                    }
                    ?>
                </div>
                <form class="comment-form stack-form" method="post" action="/blog/<?= e($post['slug']) ?>/comments" id="comment-form">
                    <h3 data-comment-title>Leave a comment</h3>
                    <?= Csrf::field() ?>
                    <input type="hidden" name="parent_id" value="<?= e(old('parent_id')) ?>" data-parent>
                    <input type="hidden" name="form_started" value="<?= time() ?>">
                    <div class="hp" aria-hidden="true">
                        <label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off"></label>
                    </div>
                    <div class="form-row">
                        <label>Name
                            <input name="name" required maxlength="120" value="<?= e(old('name', $currentUser['name'] ?? '')) ?>">
                            <?= field_error('name') ?>
                        </label>
                        <label>Email
                            <input type="email" name="email" required maxlength="190" value="<?= e(old('email', $currentUser['email'] ?? '')) ?>">
                            <?= field_error('email') ?>
                        </label>
                    </div>
                    <label>Comment
                        <textarea name="content" required minlength="4" maxlength="5000" rows="5"><?= e(old('content')) ?></textarea>
                        <?= field_error('content') ?>
                    </label>
                    <p class="fine">Your email is never published. New comments stay in review until an editor approves them.</p>
                    <button class="btn btn-primary" type="submit">Submit comment</button>
                </form>
            </section>
        </div>
        <aside class="article-rail">
            <?php if ($toc): ?>
                <nav class="toc" aria-label="Table of contents">
                    <p>In this story</p>
                    <ol>
                        <?php foreach ($toc as $item): ?>
                            <li class="<?= $item['level'] === 'h3' ? 'is-sub' : '' ?>"><a href="#<?= e($item['id']) ?>"><?= e($item['text']) ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>
            <div class="rail-card">
                <p class="kicker">The briefing</p>
                <h2>Get the next story first.</h2>
                <?php partial('newsletter', ['compact' => true]); ?>
            </div>
        </aside>
    </div>
</article>
<div class="read-progress" data-progress></div>
<?php if ($related): ?>
<section class="section section--paper">
    <div class="container">
        <div class="section-head"><h2>Related stories</h2></div>
        <div class="card-grid card-grid--3">
            <?php foreach ($related as $item): ?>
                <?php partial('post-card', ['post' => $item]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
