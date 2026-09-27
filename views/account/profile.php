<section class="page-hero">
    <div class="container narrow">
        <p class="kicker">Account</p>
        <h1>Your profile</h1>
    </div>
</section>
<section class="section">
    <div class="container narrow">
        <form class="stack-form card-form" method="post" action="/account" enctype="multipart/form-data">
            <?= Csrf::field() ?>
            <label>Name<input name="name" required value="<?= e(old('name', $user['name'])) ?>"><?= field_error('name') ?></label>
            <label>Bio<textarea name="bio" rows="4" maxlength="2000"><?= e(old('bio', (string) ($user['bio'] ?? ''))) ?></textarea></label>
            <label>Website<input name="website" value="<?= e(old('website', (string) ($user['website'] ?? ''))) ?>" placeholder="https://"></label>
            <label>X handle<input name="twitter" value="<?= e(old('twitter', (string) ($user['twitter'] ?? ''))) ?>"></label>
            <label>LinkedIn URL<input name="linkedin" value="<?= e(old('linkedin', (string) ($user['linkedin'] ?? ''))) ?>"></label>
            <label>Avatar<input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp"></label>
            <label>New password <span class="fine">(leave blank to keep the current one)</span>
                <input type="password" name="password" autocomplete="new-password" data-password>
                <?= field_error('password') ?>
            </label>
            <label>Confirm new password<input type="password" name="password_confirmation" autocomplete="new-password"></label>
            <button class="btn btn-primary" type="submit">Save profile</button>
        </form>
        <form method="post" action="/logout" class="logout-form"><?= Csrf::field() ?><button class="text-btn" type="submit">Sign out</button></form>
    </div>
</section>
