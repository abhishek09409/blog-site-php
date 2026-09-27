<form class="panel stack narrow" method="post" action="/admin/profile" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <label>Name<input name="name" required value="<?= e(old('name', $user['name'])) ?>"></label>
    <label>Bio<textarea name="bio" rows="4"><?= e(old('bio', (string) ($user['bio'] ?? ''))) ?></textarea></label>
    <label>Website<input name="website" value="<?= e(old('website', (string) ($user['website'] ?? ''))) ?>"></label>
    <label>X handle<input name="twitter" value="<?= e(old('twitter', (string) ($user['twitter'] ?? ''))) ?>"></label>
    <label>LinkedIn<input name="linkedin" value="<?= e(old('linkedin', (string) ($user['linkedin'] ?? ''))) ?>"></label>
    <label>Avatar<input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp"></label>
    <label>New password<input type="password" name="password" autocomplete="new-password"><?= field_error('password') ?></label>
    <label>Confirm password<input type="password" name="password_confirmation" autocomplete="new-password"></label>
    <button class="btn" type="submit">Save profile</button>
</form>
