<section class="auth-shell">
    <div class="auth-card">
        <p class="kicker">Newsroom</p>
        <h1>Sign in</h1>
        <p class="muted">Writers and editors use the same door as readers.</p>
        <form class="stack-form" method="post" action="/login">
            <?= Csrf::field() ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">
            <label>Email
                <input type="email" name="email" required value="<?= e(old('email')) ?>" autocomplete="username">
                <?= field_error('email') ?>
            </label>
            <label>Password
                <span class="password-field">
                    <input type="password" name="password" required autocomplete="current-password" data-password>
                    <button type="button" class="text-btn" data-password-toggle>Show</button>
                </span>
                <?= field_error('password') ?>
            </label>
            <label class="check"><input type="checkbox" name="remember" value="1"> Remember this browser</label>
            <button class="btn btn-primary" type="submit">Sign in</button>
        </form>
        <p class="auth-links"><a href="/forgot-password">Forgot password</a><a href="/register">Create an account</a></p>
    </div>
</section>
