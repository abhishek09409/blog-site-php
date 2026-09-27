</main>
<?php
$year = date('Y');
$socials = array_filter([
    'X' => social_href('twitter', 'https://x.com/'),
    'Instagram' => social_href('instagram', 'https://instagram.com/'),
    'LinkedIn' => social_href('linkedin', 'https://www.linkedin.com/in/'),
    'YouTube' => social_href('youtube', 'https://www.youtube.com/@'),
    'Facebook' => social_href('facebook', 'https://facebook.com/'),
]);
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="logo logo--light" href="/">
                <img src="/assets/images/logo-mark.svg" alt="" width="36" height="36">
                <span><?= e(site_name()) ?></span>
            </a>
            <p class="footer-about"><?= e(setting('footer_text', setting('site_description', 'Meridian publishes original reporting and essays.'))) ?></p>
        </div>
        <div>
            <h2>Sections</h2>
            <ul>
                <?php foreach ($headerCategories as $category): ?>
                    <li><a href="/category/<?= e($category['slug']) ?>"><?= e($category['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h2>The desk</h2>
            <ul>
                <?php if (!empty($footerMenu)): ?>
                    <?php foreach ($footerMenu as $item): ?>
                        <li><a href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><a href="/about">About</a></li>
                    <li><a href="/contact">Contact</a></li>
                    <li><a href="/privacy">Privacy</a></li>
                    <li><a href="/terms">Terms</a></li>
                    <li><a href="/sitemap.xml">Sitemap</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div>
            <h2>Newsletter</h2>
            <p>One briefing, a few times a month. No daily noise.</p>
            <?php partial('newsletter', ['compact' => true]); ?>
            <?php if ($socials): ?>
                <div class="social-row">
                    <?php foreach ($socials as $label => $href): ?>
                        <a href="<?= e($href) ?>" rel="noopener noreferrer" target="_blank"><?= e($label) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="container footer-bottom">
        <p>&copy; <?= e($year) ?> <?= e(site_name()) ?>. All rights reserved.</p>
        <?php if ($address = setting('address', '')): ?>
            <p><?= e($address) ?></p>
        <?php endif; ?>
    </div>
</footer>
<script src="<?= e(asset('assets/js/app.js')) ?>"></script>
</body>
</html>
