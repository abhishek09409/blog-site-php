<section class="auth-shell">
    <div class="auth-card">
        <p class="kicker">Readers</p>
        <h1>Create an account</h1>
        <p class="muted">Reader accounts can comment with a saved name. Publishing access is granted by an editor.</p>
        <form class="stack-form" method="post" action="/register">
            <?= Csrf::field() ?>
            <label>Name<input name="name" required maxlength="120" value="<?= e(old('name')) ?>"><?= field_error('name') ?></label>
            <label>Username<input name="username" required maxlength="60" value="<?= e(old('username')) ?>" placeholder="jane-doe"><?= field_error('username') ?></label>
            <label>Email<input type="email" name="email" required value="<?= e(old('email')) ?>"><?= field_error('email') ?></label>
            <label>Password
                <span class="password-field">
                    <input type="password" name="password" required minlength="10" data-password autocomplete="new-password">
                    <button type="button" class="text-btn" data-password-toggle>Show</button>
                </span>
                <?= field_error('password') ?>
            </label>
            <label>Confirm password<input type="password" name="password_confirmation" required minlength="10" autocomplete="new-password"></label>
            <button class="btn btn-primary" type="submit">Create account</button>
        </form>
        <p class="auth-links"><a href="/login">Already have an account</a></p>
    </div>
</section>
