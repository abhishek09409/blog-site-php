<section class="auth-shell">
    <div class="auth-card">
        <p class="kicker">Account</p>
        <h1>Choose a new password</h1>
        <form class="stack-form" method="post" action="/reset-password/<?= e($token) ?>">
            <?= Csrf::field() ?>
            <label>New password
                <span class="password-field">
                    <input type="password" name="password" required minlength="10" data-password autocomplete="new-password">
                    <button type="button" class="text-btn" data-password-toggle>Show</button>
                </span>
                <?= field_error('password') ?>
            </label>
            <label>Confirm password<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
            <button class="btn btn-primary" type="submit">Update password</button>
        </form>
    </div>
</section>
