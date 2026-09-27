<?php

declare(strict_types=1);

class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect($this->destination());
        }
        view('auth/login', [
            'next' => $this->safeNext(),
            'seo' => ['title' => 'Sign in', 'robots' => 'noindex,nofollow'],
        ]);
    }

    public function login(): void
    {
        Csrf::verify();
        $validator = Validator::make($_POST, [
            'email' => 'required|email',
            'password' => 'required|min:8|max:200',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/login');
        }
        try {
            $ok = Auth::attempt((string) $_POST['email'], (string) $_POST['password'], !empty($_POST['remember']));
        } catch (HttpException $e) {
            flash('error', $e->getMessage());
            set_old($_POST);
            redirect('/login');
        }
        if (!$ok) {
            flash('error', 'Those credentials do not match our records.');
            set_old($_POST);
            redirect('/login');
        }
        flash('success', 'Welcome back.');
        redirect($this->destination());
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            redirect('/account');
        }
        view('auth/register', [
            'seo' => ['title' => 'Create an account', 'robots' => 'noindex,nofollow'],
        ]);
    }

    public function register(): void
    {
        if (!RateLimiter::hit('register:' . client_ip(), 5, 900)) {
            flash('error', 'Too many accounts created from this network. Try again later.');
            redirect('/register');
        }
        Csrf::verify();
        $validator = Validator::make($_POST, [
            'name' => 'required|max:120',
            'username' => 'required|username|max:60',
            'email' => 'required|email|max:190',
            'password' => 'required|min:10|password|confirmed',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/register');
        }
        $username = slugify((string) $_POST['username']);
        $email = mb_strtolower(trim((string) $_POST['email']));
        if (User::findByEmail($email) || db()->fetch('SELECT id FROM users WHERE username = ?', [$username])) {
            back_with_errors(['email' => ['An account with that email or username already exists.']], $_POST, '/register');
        }
        $role = db()->fetch("SELECT id FROM roles WHERE name = 'User'");
        if (!$role) {
            throw new HttpException('Registration is unavailable.', 500);
        }
        $id = User::create([
            'name' => trim((string) $_POST['name']),
            'username' => unique_slug('users', $username),
            'email' => $email,
            'password' => (string) $_POST['password'],
            'role_id' => (int) $role['id'],
            'status' => 'active',
        ]);
        Auth::loginUser($id, false);
        ActivityLog::record('auth.register', 'Created an account', $id);
        flash('success', 'Your account is ready.');
        redirect('/account');
    }

    public function logout(): void
    {
        if (is_post()) {
            Csrf::verify();
        }
        Auth::logout();
        redirect('/');
    }

    public function forgotForm(): void
    {
        view('auth/forgot', [
            'seo' => ['title' => 'Forgot password', 'robots' => 'noindex,nofollow'],
        ]);
    }

    public function forgot(): void
    {
        Csrf::verify();
        if (!RateLimiter::hit('reset:' . client_ip(), 5, 900)) {
            flash('error', 'Too many reset requests. Try again later.');
            redirect('/forgot-password');
        }
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? User::findByEmail($email) : null;
        if ($user && $user['status'] === 'active') {
            $token = bin2hex(random_bytes(32));
            db()->delete('password_resets', 'email = ?', [$email]);
            db()->insert('password_resets', [
                'email' => $email,
                'token_hash' => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            ]);
            $link = url('reset-password/' . $token);
            Mailer::send($email, site_name() . ' password reset', "Use this link within one hour to choose a new password:\n\n{$link}\n\nIf you did not ask for this, ignore the email.");
        }
        flash('success', 'If that account exists, a reset link is on its way.');
        redirect('/forgot-password');
    }

    public function resetForm(array $params): void
    {
        $this->resetRow((string) $params['token']);
        view('auth/reset', [
            'token' => (string) $params['token'],
            'seo' => ['title' => 'Choose a new password', 'robots' => 'noindex,nofollow'],
        ]);
    }

    public function reset(array $params): void
    {
        Csrf::verify();
        $row = $this->resetRow((string) $params['token']);
        $validator = Validator::make($_POST, [
            'password' => 'required|min:10|password|confirmed',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), [], '/reset-password/' . $params['token']);
        }
        $user = User::findByEmail((string) $row['email']);
        if (!$user) {
            throw new HttpException('This reset link is no longer valid.', 404);
        }
        User::update((int) $user['id'], ['password' => (string) $_POST['password']]);
        db()->delete('password_resets', 'email = ?', [(string) $row['email']]);
        db()->delete('remember_tokens', 'user_id = ?', [(int) $user['id']]);
        flash('success', 'Password updated. Sign in with the new one.');
        redirect('/login');
    }

    public function account(): void
    {
        if (!Auth::check()) {
            redirect('/login?next=/account');
        }
        view('account/profile', [
            'user' => Auth::user(),
            'seo' => ['title' => 'Your account', 'robots' => 'noindex,nofollow'],
        ]);
    }

    public function updateAccount(): void
    {
        if (!Auth::check()) {
            redirect('/login');
        }
        Csrf::verify();
        $user = Auth::user();
        $validator = Validator::make($_POST, [
            'name' => 'required|max:120',
            'bio' => 'max:2000',
            'website' => 'max:255',
            'twitter' => 'max:120',
            'linkedin' => 'max:255',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/account');
        }
        $data = [
            'name' => trim((string) $_POST['name']),
            'bio' => trim((string) ($_POST['bio'] ?? '')),
            'website' => trim((string) ($_POST['website'] ?? '')),
            'twitter' => trim((string) ($_POST['twitter'] ?? '')),
            'linkedin' => trim((string) ($_POST['linkedin'] ?? '')),
        ];
        if ($data['website'] !== '' && !safe_url($data['website'])) {
            back_with_errors(['website' => ['Enter a valid URL.']], $_POST, '/account');
        }
        if (!empty($_POST['password'])) {
            $check = Validator::make($_POST, ['password' => 'min:10|password|confirmed']);
            if ($check->fails()) {
                back_with_errors($check->errors(), $_POST, '/account');
            }
            $data['password'] = (string) $_POST['password'];
        }
        if (!empty($_FILES['avatar']['name'])) {
            try {
                $media = Uploader::storeImage($_FILES['avatar'], (int) $user['id'], $user['name']);
                $data['avatar'] = $media['filepath'];
            } catch (RuntimeException $e) {
                flash('error', $e->getMessage());
                set_old($_POST);
                redirect('/account');
            }
        }
        User::update((int) $user['id'], $data);
        flash('success', 'Account updated.');
        redirect('/account');
    }

    private function resetRow(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            throw new HttpException('This reset link is not valid.', 404);
        }
        $row = db()->fetch(
            'SELECT * FROM password_resets WHERE token_hash = ? AND expires_at > NOW()',
            [hash('sha256', $token)]
        );
        if (!$row) {
            throw new HttpException('This reset link has expired.', 404);
        }
        return $row;
    }

    private function safeNext(): string
    {
        $next = (string) ($_GET['next'] ?? $_POST['next'] ?? '');
        if ($next !== '' && str_starts_with($next, '/') && !str_starts_with($next, '//')) {
            return $next;
        }
        return '';
    }

    private function destination(): string
    {
        $next = $this->safeNext();
        if ($next !== '') {
            return $next;
        }
        return Auth::can('dashboard') ? '/admin' : '/account';
    }
}
