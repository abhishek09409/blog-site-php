<?php

declare(strict_types=1);

class NewsletterAdmin
{
    public function index(): void
    {
        admin_require('newsletter.view');
        admin_view('newsletter', [
            'title' => 'Newsletter',
            'subscribers' => Newsletter::all(),
        ]);
    }

    public function status(array $params): void
    {
        admin_require('newsletter.view');
        Csrf::verify();
        $status = (string) ($_POST['status'] ?? '');
        if (!in_array($status, ['active', 'unsubscribed'], true)) {
            throw new HttpException('Unknown status.', 422);
        }
        Newsletter::setStatus((int) $params['id'], $status);
        flash('success', 'Subscriber updated.');
        redirect('/admin/newsletter');
    }

    public function delete(array $params): void
    {
        admin_require('newsletter.view');
        Csrf::verify();
        Newsletter::delete((int) $params['id']);
        flash('success', 'Subscriber removed.');
        redirect('/admin/newsletter');
    }

    public function export(): void
    {
        admin_require('newsletter.view');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="meridian-newsletter.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['email', 'status', 'created_at']);
        foreach (Newsletter::all() as $row) {
            fputcsv($out, [$row['email'], $row['status'], $row['created_at']]);
        }
        fclose($out);
        exit;
    }
}

class MessageAdmin
{
    public function index(): void
    {
        admin_require('messages.view');
        admin_view('messages', [
            'title' => 'Contact messages',
            'messages' => ContactMessage::all(),
            'open' => isset($_GET['id']) ? ContactMessage::find((int) $_GET['id']) : null,
        ]);
    }

    public function read(array $params): void
    {
        admin_require('messages.view');
        $message = ContactMessage::find((int) $params['id']);
        if (!$message) {
            throw new HttpException('Message not found.', 404);
        }
        ContactMessage::markRead((int) $message['id']);
        redirect('/admin/messages?id=' . $message['id']);
    }

    public function delete(array $params): void
    {
        admin_require('messages.view');
        Csrf::verify();
        ContactMessage::delete((int) $params['id']);
        flash('success', 'Message deleted.');
        redirect('/admin/messages');
    }
}

class MenuAdmin
{
    public function index(): void
    {
        admin_require('menus.edit');
        admin_view('menus', [
            'title' => 'Menus',
            'menus' => Menu::all(),
        ]);
    }

    public function save(): void
    {
        admin_require('menus.edit');
        Csrf::verify();
        $validator = Validator::make($_POST, [
            'menu_id' => 'required|int',
            'label' => 'required|max:120',
            'url' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/admin/menus');
        }
        $url = trim((string) $_POST['url']);
        if (!str_starts_with($url, '/') && !safe_url($url)) {
            back_with_errors(['url' => ['Use a site path or a full URL.']], $_POST, '/admin/menus');
        }
        $sort = (int) ($_POST['sort_order'] ?? 0);
        $itemId = (int) ($_POST['item_id'] ?? 0);
        if ($itemId) {
            Menu::updateItem($itemId, trim((string) $_POST['label']), $url, $sort);
        } else {
            Menu::addItem((int) $_POST['menu_id'], trim((string) $_POST['label']), $url, $sort);
        }
        flash('success', 'Menu saved.');
        redirect('/admin/menus');
    }

    public function delete(array $params): void
    {
        admin_require('menus.edit');
        Csrf::verify();
        Menu::deleteItem((int) $params['id']);
        flash('success', 'Menu item removed.');
        redirect('/admin/menus');
    }
}

class SettingAdmin
{
    public function index(): void
    {
        admin_require('settings.edit');
        admin_view('settings', ['title' => 'Settings']);
    }

    public function save(): void
    {
        admin_require('settings.edit');
        Csrf::verify();
        $keys = [
            'site_name', 'site_description', 'primary_color', 'email', 'phone', 'address',
            'facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'footer_text',
            'ga_id', 'gsc_verification', 'seo_title', 'seo_description',
        ];
        $pairs = [];
        foreach ($keys as $key) {
            $pairs[$key] = trim((string) ($_POST[$key] ?? ''));
        }
        if ($pairs['site_name'] === '') {
            back_with_errors(['site_name' => ['Site name is required.']], $_POST, '/admin/settings');
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $pairs['primary_color'])) {
            $pairs['primary_color'] = '#F05A2A';
        }
        foreach (['facebook', 'instagram', 'twitter', 'linkedin', 'youtube'] as $social) {
            if ($pairs[$social] !== '' && !safe_url($pairs[$social]) && !preg_match('/^@?[A-Za-z0-9_\.]{2,80}$/', $pairs[$social])) {
                back_with_errors([$social => ['Enter a profile URL or handle.']], $_POST, '/admin/settings');
            }
        }
        if ($pairs['ga_id'] !== '' && !preg_match('/^(G|UA|GT)-[A-Z0-9-]+$/', $pairs['ga_id'])) {
            back_with_errors(['ga_id' => ['Use a measurement ID such as G-XXXX.']], $_POST, '/admin/settings');
        }
        if ($pairs['gsc_verification'] !== '' && !preg_match('/^[A-Za-z0-9_-]{10,120}$/', $pairs['gsc_verification'])) {
            back_with_errors(['gsc_verification' => ['That verification code looks invalid.']], $_POST, '/admin/settings');
        }
        $pairs['logo'] = setting('logo', '');
        $pairs['favicon'] = setting('favicon', '');
        foreach (['logo' => 'logo', 'favicon' => 'favicon'] as $field => $settingKey) {
            if (!empty($_FILES[$field]['name'])) {
                try {
                    $media = Uploader::storeImage($_FILES[$field], Auth::id(), site_name());
                    $pairs[$settingKey] = $media['filepath'];
                } catch (RuntimeException $e) {
                    back_with_errors([$field => [$e->getMessage()]], $_POST, '/admin/settings');
                }
            }
        }
        Setting::setMany($pairs);
        ActivityLog::record('settings.update', 'Updated site settings');
        flash('success', 'Settings saved.');
        redirect('/admin/settings');
    }
}

class ProfileAdmin
{
    public function index(): void
    {
        admin_user();
        admin_view('profile', [
            'title' => 'Profile',
            'user' => Auth::user(),
        ]);
    }

    public function save(): void
    {
        $user = admin_user();
        Csrf::verify();
        $validator = Validator::make($_POST, [
            'name' => 'required|max:120',
            'bio' => 'max:2000',
            'website' => 'max:255',
            'twitter' => 'max:120',
            'linkedin' => 'max:255',
        ]);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/admin/profile');
        }
        $data = [
            'name' => trim((string) $_POST['name']),
            'bio' => trim((string) ($_POST['bio'] ?? '')),
            'website' => trim((string) ($_POST['website'] ?? '')),
            'twitter' => trim((string) ($_POST['twitter'] ?? '')),
            'linkedin' => trim((string) ($_POST['linkedin'] ?? '')),
        ];
        if ($data['website'] !== '' && !safe_url($data['website'])) {
            back_with_errors(['website' => ['Enter a valid website URL.']], $_POST, '/admin/profile');
        }
        if (!empty($_POST['password'])) {
            $check = Validator::make($_POST, ['password' => 'min:10|password|confirmed']);
            if ($check->fails()) {
                back_with_errors($check->errors(), $_POST, '/admin/profile');
            }
            $data['password'] = (string) $_POST['password'];
        }
        if (!empty($_FILES['avatar']['name'])) {
            try {
                $media = Uploader::storeImage($_FILES['avatar'], (int) $user['id'], $user['name']);
                $data['avatar'] = $media['filepath'];
            } catch (RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('/admin/profile');
            }
        }
        User::update((int) $user['id'], $data);
        flash('success', 'Profile updated.');
        redirect('/admin/profile');
    }
}
