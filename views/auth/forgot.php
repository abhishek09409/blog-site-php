<section class="auth-shell">
    <div class="auth-card">
        <p class="kicker">Account</p>
        <h1>Reset your password</h1>
        <p class="muted">If the email matches an account, we will send a one-hour reset link.</p>
        <form class="stack-form" method="post" action="/forgot-password">
            <?= Csrf::field() ?>
            <label>Email<input type="email" name="email" required value="<?= e(old('email')) ?>"></label>
            <button class="btn btn-primary" type="submit">Send reset link</button>
        </form>
        <p class="auth-links"><a href="/login">Back to sign in</a></p>
    </div>
</section>
