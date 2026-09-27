<?php

declare(strict_types=1);

class PageController
{
    public function about(): void
    {
        view('pages/about', [
            'seo' => [
                'title' => 'About',
                'description' => site_name() . ' is an independent publication covering technology, business, money, and the places we live.',
                'canonical' => url('about'),
            ],
        ]);
    }

    public function contact(): void
    {
        view('pages/contact', [
            'seo' => [
                'title' => 'Contact',
                'description' => 'Write to the ' . site_name() . ' newsroom.',
                'canonical' => url('contact'),
            ],
        ]);
    }

    public function sendContact(): void
    {
        if (!RateLimiter::hit('contact:' . client_ip(), 5, 900)) {
            flash('error', 'Too many messages. Please try again shortly.');
            redirect('/contact');
        }
        Csrf::verify();
        if (!empty($_POST['company_website'])) {
            flash('success', 'Thanks. Your message is with the newsroom.');
            redirect('/contact');
        }
        $validator = Validator::make($_POST, [
            'name' => 'required|max:120',
            'email' => 'required|email|max:190',
            'subject' => 'required|max:180',
            'message' => 'required|min:10|max:5000',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/contact');
        }
        ContactMessage::create([
            'name' => trim((string) $_POST['name']),
            'email' => mb_strtolower(trim((string) $_POST['email'])),
            'subject' => trim((string) $_POST['subject']),
            'message' => trim((string) $_POST['message']),
            'status' => 'unread',
        ]);
        flash('success', 'Thanks. Your message is with the newsroom.');
        redirect('/contact');
    }

    public function privacy(): void
    {
        view('pages/privacy', [
            'seo' => [
                'title' => 'Privacy Policy',
                'description' => 'How ' . site_name() . ' collects, uses, and stores personal information.',
                'canonical' => url('privacy'),
            ],
        ]);
    }

    public function terms(): void
    {
        view('pages/terms', [
            'seo' => [
                'title' => 'Terms & Conditions',
                'description' => 'The terms that govern use of ' . site_name() . '.',
                'canonical' => url('terms'),
            ],
        ]);
    }

    public function subscribe(): void
    {
        $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')
            || str_contains($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest');
        $data = request_data();
        if (!Csrf::check((string) ($data['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')))) {
            if ($wantsJson) {
                json_response(['ok' => false, 'message' => 'The form expired. Refresh and try again.'], 419);
            }
            throw new HttpException('The form expired. Refresh the page and try again.', 419);
        }
        if (!RateLimiter::hit('newsletter:' . client_ip(), 8, 600)) {
            $message = 'Too many attempts. Try again shortly.';
            if ($wantsJson) {
                json_response(['ok' => false, 'message' => $message], 429);
            }
            flash('error', $message);
            redirect(back_url('/'));
        }
        $email = trim((string) ($data['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($wantsJson) {
                json_response(['ok' => false, 'message' => 'Enter a valid email address.'], 422);
            }
            flash('error', 'Enter a valid email address.');
            redirect(back_url('/'));
        }
        $result = Newsletter::subscribe($email);
        $message = match ($result) {
            'already' => 'You are already on the list.',
            'resubscribed' => 'Welcome back. Your subscription is active again.',
            default => 'You are subscribed. Watch your inbox.',
        };
        if ($wantsJson) {
            json_response(['ok' => true, 'message' => $message]);
        }
        flash('success', $message);
        redirect(back_url('/'));
    }
}
