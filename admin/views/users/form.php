<?php $editing = $editing ?? null; ?>
<form class="panel stack narrow" method="post" action="<?= $editing ? '/admin/users/edit/' . (int) $editing['id'] : '/admin/users/create' ?>">
    <?= Csrf::field() ?>
    <label>Name<input name="name" required value="<?= e(old('name', $editing['name'] ?? '')) ?>"><?= field_error('name') ?></label>
    <label>Username<input name="username" required value="<?= e(old('username', $editing['username'] ?? '')) ?>"><?= field_error('username') ?></label>
    <label>Email<input type="email" name="email" required value="<?= e(old('email', $editing['email'] ?? '')) ?>"><?= field_error('email') ?></label>
    <label>Role
        <select name="role_id">
            <?php foreach ($roles as $role): ?>
                <option value="<?= (int) $role['id'] ?>" <?= selected(old('role_id', $editing['role_id'] ?? ''), $role['id']) ?>><?= e($role['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Status
        <select name="status">
            <?php foreach (['active', 'inactive', 'banned'] as $status): ?>
                <option value="<?= e($status) ?>" <?= selected(old('status', $editing['status'] ?? 'active'), $status) ?>><?= e(ucfirst($status)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Bio<textarea name="bio" rows="4"><?= e(old('bio', $editing['bio'] ?? '')) ?></textarea></label>
    <label>Password <?= $editing ? '<small>(leave blank to keep)</small>' : '' ?>
        <input type="password" name="password" <?= $editing ? '' : 'required' ?> autocomplete="new-password">
        <?= field_error('password') ?>
    </label>
    <label>Confirm password<input type="password" name="password_confirmation" autocomplete="new-password"></label>
    <button class="btn" type="submit">Save user</button>
</form>
