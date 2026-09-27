<section class="page-hero">
    <div class="container">
        <p class="kicker">Contact</p>
        <h1>Write to the newsroom.</h1>
        <p class="lede">Tips, corrections, and general notes. We read everything. We cannot reply to every note.</p>
    </div>
</section>
<section class="section">
    <div class="container contact-layout">
        <form class="stack-form card-form" method="post" action="/contact">
            <?= Csrf::field() ?>
            <div class="hp" aria-hidden="true"><label>Company<input name="company_website" tabindex="-1" autocomplete="off"></label></div>
            <label>Name
                <input name="name" required maxlength="120" value="<?= e(old('name', $currentUser['name'] ?? '')) ?>">
                <?= field_error('name') ?>
            </label>
            <label>Email
                <input type="email" name="email" required maxlength="190" value="<?= e(old('email', $currentUser['email'] ?? '')) ?>">
                <?= field_error('email') ?>
            </label>
            <label>Subject
                <input name="subject" required maxlength="180" value="<?= e(old('subject')) ?>">
                <?= field_error('subject') ?>
            </label>
            <label>Message
                <textarea name="message" required minlength="10" maxlength="5000" rows="7"><?= e(old('message')) ?></textarea>
                <?= field_error('message') ?>
            </label>
            <button class="btn btn-primary" type="submit">Send message</button>
        </form>
        <aside class="contact-aside">
            <?php if ($email = setting('email', '')): ?><p><strong>Email</strong><br><?= e($email) ?></p><?php endif; ?>
            <?php if ($phone = setting('phone', '')): ?><p><strong>Phone</strong><br><?= e($phone) ?></p><?php endif; ?>
            <?php if ($address = setting('address', '')): ?><p><strong>Desk</strong><br><?= e($address) ?></p><?php endif; ?>
            <p class="muted">For account help, sign in and use the profile page. Password resets are sent from the sign-in screen.</p>
        </aside>
    </div>
</section>
