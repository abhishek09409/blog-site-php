<?php

declare(strict_types=1);

class UserAdmin
{
    public function index(): void
    {
        admin_require('users.manage');
        admin_view('users/index', [
            'title' => 'Users',
            'users' => User::all(),
        ]);
    }

    public function create(): void
    {
        admin_require('users.manage');
        admin_view('users/form', [
            'title' => 'Add user',
            'editing' => null,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(): void
    {
        admin_require('users.manage');
        Csrf::verify();
        $validator = Validator::make($_POST, [
            'name' => 'required|max:120',
            'username' => 'required|username|max:60',
            'email' => 'required|email|max:190',
            'password' => 'required|min:10|password|confirmed',
            'role_id' => 'required|int',
            'status' => 'required|in:active,inactive,banned',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/admin/users/create');
        }
        $this->guardRole((int) $_POST['role_id']);
        if (User::findByEmail((string) $_POST['email']) || db()->fetch('SELECT id FROM users WHERE username = ?', [slugify((string) $_POST['username'])])) {
            back_with_errors(['email' => ['That email or username is already in use.']], $_POST, '/admin/users/create');
        }
        $id = User::create($this->payload(true));
        ActivityLog::record('user.create', 'Created user #' . $id);
        flash('success', 'User created.');
        redirect('/admin/users');
    }

    public function edit(array $params): void
    {
        admin_require('users.manage');
        $editing = $this->editable((int) $params['id']);
        admin_view('users/form', [
            'title' => 'Edit user',
            'editing' => $editing,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(array $params): void
    {
        admin_require('users.manage');
        Csrf::verify();
        $editing = $this->editable((int) $params['id']);
        $rules = [
            'name' => 'required|max:120',
            'username' => 'required|username|max:60',
            'email' => 'required|email|max:190',
            'role_id' => 'required|int',
            'status' => 'required|in:active,inactive,banned',
        ];
        if (!empty($_POST['password'])) {
            $rules['password'] = 'min:10|password|confirmed';
        }
        $validator = Validator::make($_POST, $rules);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/admin/users/edit/' . $editing['id']);
        }
        $this->guardRole((int) $_POST['role_id'], $editing);
        $username = slugify((string) $_POST['username']);
        $email = mb_strtolower(trim((string) $_POST['email']));
        $clash = db()->fetch('SELECT id FROM users WHERE (email = ? OR username = ?) AND id <> ?', [$email, $username, (int) $editing['id']]);
        if ($clash) {
            back_with_errors(['email' => ['That email or username is already in use.']], $_POST, '/admin/users/edit/' . $editing['id']);
        }
        User::update((int) $editing['id'], $this->payload(false));
        ActivityLog::record('user.update', 'Updated ' . $email);
        flash('success', 'User updated.');
        redirect('/admin/users');
    }

    public function delete(array $params): void
    {
        admin_require('users.manage');
        Csrf::verify();
        $editing = $this->editable((int) $params['id']);
        if ((int) $editing['id'] === Auth::id()) {
            flash('error', 'You cannot delete your own account.');
            redirect('/admin/users');
        }
        try {
            User::delete((int) $editing['id']);
            flash('success', 'User deleted.');
        } catch (Throwable) {
            flash('error', 'Reassign this person’s posts before deleting the account.');
        }
        redirect('/admin/users');
    }

    private function payload(bool $creating): array
    {
        $data = [
            'name' => trim((string) $_POST['name']),
            'username' => slugify((string) $_POST['username']),
            'email' => mb_strtolower(trim((string) $_POST['email'])),
            'role_id' => (int) $_POST['role_id'],
            'status' => (string) $_POST['status'],
            'bio' => trim((string) ($_POST['bio'] ?? '')),
        ];
        if ($creating || !empty($_POST['password'])) {
            $data['password'] = (string) $_POST['password'];
        }
        return $data;
    }

    private function assignableRoles(): array
    {
        $roles = User::roles();
        if ((Auth::user()['role_name'] ?? '') !== 'Super Admin') {
            $roles = array_values(array_filter($roles, static fn (array $role): bool => $role['name'] !== 'Super Admin'));
        }
        return $roles;
    }

    private function guardRole(int $roleId, ?array $existing = null): void
    {
        $role = db()->fetch('SELECT * FROM roles WHERE id = ?', [$roleId]);
        if (!$role) {
            throw new HttpException('Unknown role.', 422);
        }
        $actor = Auth::user();
        if ($role['name'] === 'Super Admin' && ($actor['role_name'] ?? '') !== 'Super Admin') {
            throw new HttpException('Only a super admin can assign that role.', 403);
        }
        if ($existing && $existing['role_name'] === 'Super Admin' && ($actor['role_name'] ?? '') !== 'Super Admin') {
            throw new HttpException('Only a super admin can edit that account.', 403);
        }
    }

    private function editable(int $id): array
    {
        $user = User::find($id);
        if (!$user) {
            throw new HttpException('User not found.', 404);
        }
        if ($user['role_name'] === 'Super Admin' && (Auth::user()['role_name'] ?? '') !== 'Super Admin') {
            throw new HttpException('Only a super admin can edit that account.', 403);
        }
        return $user;
    }
}
