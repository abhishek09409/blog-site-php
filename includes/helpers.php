<?php

declare(strict_types=1);

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return $value;
}

function config(string $key, mixed $default = null): mixed
{
    static $items = null;
    if ($items === null) {
        $items = [
            'app' => require BASE_PATH . '/config/app.php',
            'database' => require BASE_PATH . '/config/database.php',
        ];
    }
    $value = $items;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function base_path(string $path = ''): string
{
    return BASE_PATH . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_base(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $configured = trim((string) env('APP_BASE', ''), '/');
    if ($configured === '') {
        $urlPath = parse_url((string) config('app.url'), PHP_URL_PATH) ?: '';
        $configured = trim((string) $urlPath, '/');
    }
    if ($configured === '') {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        if (preg_match('#^(.*)/(?:index|admin/index|api/[a-z0-9_-]+)\.php$#', $script, $matches)) {
            $configured = trim($matches[1], '/');
        }
    }
    $base = $configured === '' ? '' : '/' . $configured;
    return $base;
}

function href(string $path = '/'): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $base = app_base();
    $hash = '';
    $query = '';
    if (str_contains($path, '#')) {
        [$path, $hash] = explode('#', $path, 2);
        $hash = '#' . $hash;
    }
    if (str_contains($path, '?')) {
        [$path, $query] = explode('?', $path, 2);
        $query = '?' . $query;
    }
    if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
        return $path . $hash . $query;
    }
    if ($path === '' || $path === '/') {
        return ($base === '' ? '/' : $base . '/') . $hash . $query;
    }
    return $base . '/' . ltrim($path, '/') . $hash . $query;
}

function prefix_html_urls(string $html): string
{
    $base = app_base();
    if ($base === '' || $html === '') {
        return $html;
    }
    $folder = preg_quote(ltrim($base, '/'), '#');
    $updated = preg_replace(
        '#(\s(?:href|action|src)=(["\']))/(?!' . $folder . '/|/)#i',
        '$1' . $base . '/',
        $html
    );
    return is_string($updated) ? $updated : $html;
}

function url(string $path = ''): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $root = rtrim((string) config('app.url'), '/');
    $base = app_base();
    $rootPath = parse_url($root, PHP_URL_PATH) ?: '';
    if ($base !== '' && rtrim((string) $rootPath, '/') !== $base) {
        $root .= $base;
    }
    if ($path === '' || $path === '/') {
        return $root . '/';
    }
    return $root . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $relative = ltrim($path, '/');
    $full = base_path($relative);
    $version = is_file($full) ? (string) filemtime($full) : '1';
    return '/' . $relative . '?v=' . $version;
}

function redirect(string $to, int $code = 302): never
{
    if (!preg_match('#^https?://#i', $to)) {
        $to = href($to);
    }
    header('Location: ' . $to, true, $code);
    exit;
}

function slugify(string $text): string
{
    $text = trim(mb_strtolower($text));
    $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if (is_string($converted) && $converted !== '') {
        $text = $converted;
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', strtolower($text)) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? substr($text, 0, 180) : 'item';
}

function unique_slug(string $table, string $slug, ?int $ignoreId = null): string
{
    $allowed = ['posts', 'categories', 'tags', 'users'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Unsupported slug table.');
    }
    $column = $table === 'users' ? 'username' : 'slug';
    $base = $slug !== '' ? $slug : 'item';
    $candidate = $base;
    $i = 2;
    while (true) {
        $sql = "SELECT id FROM `{$table}` WHERE `{$column}` = ?";
        $params = [$candidate];
        if ($ignoreId) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        if (!db()->fetch($sql, $params)) {
            return $candidate;
        }
        $candidate = $base . '-' . $i;
        $i++;
    }
}

function reading_time(string $html): int
{
    $words = str_word_count(trim(strip_tags($html)));
    return max(1, (int) ceil($words / 220));
}

function format_date(?string $date, string $format = 'F j, Y'): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : '';
}

function excerpt_text(?string $text, int $limit = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)) ?? '');
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit - 1)) . '…';
}

function number_short(int $number): string
{
    if ($number >= 1000000) {
        return rtrim(rtrim(number_format($number / 1000000, 1), '0'), '.') . 'M';
    }
    if ($number >= 1000) {
        return rtrim(rtrim(number_format($number / 1000, 1), '0'), '.') . 'k';
    }
    return (string) $number;
}

function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return substr($ip, 0, 45);
}

function ip_hash(): string
{
    return hash('sha256', client_ip() . '|' . (string) config('app.key'));
}

function request_method(): string
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    return $method === 'HEAD' ? 'GET' : $method;
}

function is_post(): bool
{
    return request_method() === 'POST';
}

function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function request_data(): array
{
    $type = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($type, 'application/json')) {
        $decoded = json_decode(file_get_contents('php://input') ?: '', true);
        return is_array($decoded) ? $decoded : [];
    }
    return $_POST;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message === null) {
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    $_SESSION['_flash'][$key] = $message;
    return null;
}

function set_old(array $data): void
{
    unset($data['password'], $data['password_confirmation'], $data['_csrf']);
    $_SESSION['_old'] = $data;
}

function old(string $key, mixed $default = ''): string
{
    return (string) ($_SESSION['_old'][$key] ?? $default);
}

function set_errors(array $errors): void
{
    $_SESSION['_errors'] = $errors;
}

function field_error(string $field): string
{
    $message = $_SESSION['_errors'][$field][0] ?? '';
    return $message !== '' ? '<p class="field-error">' . e($message) . '</p>' : '';
}

function has_error(string $field): bool
{
    return !empty($_SESSION['_errors'][$field]);
}

function clear_old(): void
{
    unset($_SESSION['_old'], $_SESSION['_errors']);
}

function back_url(string $fallback = '/'): string
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $parts = parse_url($ref);
    if ($ref && isset($parts['host']) && strcasecmp((string) $parts['host'], $host) === 0) {
        $path = $parts['path'] ?? '/';
        if (isset($parts['query'])) {
            $path .= '?' . $parts['query'];
        }
        return $path;
    }
    return $fallback;
}

function back_with_errors(array $errors, array $old, string $fallback = '/'): never
{
    set_errors($errors);
    set_old($old);
    redirect(back_url($fallback));
}

function setting(string $key, string $default = ''): string
{
    static $all = null;
    if ($all === null) {
        $all = [];
        try {
            foreach (db()->fetchAll('SELECT setting_key, setting_value FROM settings') as $row) {
                $all[$row['setting_key']] = (string) $row['setting_value'];
            }
        } catch (Throwable) {
            $all = [];
        }
    }
    return $all[$key] ?? $default;
}

function setting_forget(): void
{
    // Settings are cached per request. A new request sees fresh values.
}

function site_name(): string
{
    return setting('site_name', (string) config('app.name'));
}

function primary_color(): string
{
    $color = setting('primary_color', '#F05A2A');
    return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : '#F05A2A';
}

function safe_url(?string $url): ?string
{
    if (!$url) {
        return null;
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $url : null;
}

function like_term(string $value): string
{
    $value = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    return '%' . $value . '%';
}

function paginate(int $total, int $page, int $perPage): array
{
    $pages = max(1, (int) ceil($total / max(1, $perPage)));
    $page = max(1, min($page, $pages));
    return [
        'total' => $total,
        'page' => $page,
        'pages' => $pages,
        'per_page' => $perPage,
        'offset' => ($page - 1) * $perPage,
    ];
}

function page_url(string $base, int $page, array $query = []): string
{
    $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');
    unset($query['page']);
    $path = $page <= 1 ? $base : rtrim($base, '/') . '/page/' . $page;
    if ($query) {
        $path .= '?' . http_build_query($query);
    }
    return $path;
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = rawurldecode($path);
    $base = app_base();
    if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
        $path = substr($path, strlen($base)) ?: '/';
    }
    $path = '/' . trim($path, '/');
    return $path === '//' ? '/' : $path;
}

function current_path(): string
{
    return request_path();
}

function nav_active(string $path): string
{
    $current = current_path();
    if ($path === '/') {
        return $current === '/' ? 'is-active' : '';
    }
    return $current === $path || str_starts_with($current, rtrim($path, '/') . '/') ? 'is-active' : '';
}

function view(string $template, array $data = [], ?string $layout = 'layouts/main'): void
{
    $seo = array_merge([
        'title' => site_name(),
        'description' => setting('seo_description', setting('site_description', 'Meridian is an independent publication on technology, business, and modern work.')),
        'canonical' => url(ltrim(current_path(), '/')),
        'image' => url(ltrim(setting('logo', 'assets/images/og-default.jpg'), '/')),
        'type' => 'website',
        'robots' => 'index,follow',
        'jsonld' => [],
    ], $data['seo'] ?? []);
    if (!str_contains($seo['title'], site_name()) && $template !== 'home') {
        $seo['title'] .= ' — ' . site_name();
    }
    $data['seo'] = $seo;
    try {
        $data['currentUser'] = Auth::user();
    } catch (Throwable) {
        $data['currentUser'] = null;
    }
    if (!isset($data['headerCategories'])) {
        try {
            $data['headerCategories'] = Category::active();
        } catch (Throwable) {
            $data['headerCategories'] = [];
        }
    }
    if (!isset($data['headerMenu'])) {
        try {
            $data['headerMenu'] = Menu::items('header');
        } catch (Throwable) {
            $data['headerMenu'] = [];
        }
    }
    if (!isset($data['footerMenu'])) {
        try {
            $data['footerMenu'] = Menu::items('footer');
        } catch (Throwable) {
            $data['footerMenu'] = [];
        }
    }
    extract($data, EXTR_SKIP);
    ob_start();
    require base_path('views/' . $template . '.php');
    $content = ob_get_clean();
    if ($layout === null) {
        echo $content;
        clear_old();
        return;
    }
    require base_path('views/' . $layout . '.php');
    clear_old();
}

function admin_view(string $template, array $data = []): void
{
    $data['adminUser'] = Auth::user();
    $data['title'] = $data['title'] ?? 'Dashboard';
    $data['unreadMessages'] = 0;
    $data['pendingComments'] = 0;
    try {
        $data['unreadMessages'] = (int) (db()->fetch("SELECT COUNT(*) c FROM contact_messages WHERE status = 'unread'")['c'] ?? 0);
        $data['pendingComments'] = (int) (db()->fetch("SELECT COUNT(*) c FROM comments WHERE status = 'pending'")['c'] ?? 0);
    } catch (Throwable) {
    }
    extract($data, EXTR_SKIP);
    ob_start();
    require base_path('admin/views/' . $template . '.php');
    $content = ob_get_clean();
    require base_path('admin/views/layout.php');
    clear_old();
}

function partial(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require base_path('views/partials/' . $name . '.php');
}

function icon(string $name, int $size = 20): string
{
    $icons = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'clock' => '<circle cx="12" cy="12" r="8"/><path d="M12 8v5l3 2"/>',
        'eye' => '<path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="2.5"/>',
        'user' => '<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z"/><path d="M4 20a8 8 0 0 1 16 0"/>',
        'mail' => '<path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/>',
        'share' => '<circle cx="6" cy="12" r="2"/><circle cx="17" cy="7" r="2"/><circle cx="17" cy="17" r="2"/><path d="M8 11l7-3M8 13l7 3"/>',
        'link' => '<path d="M10 13a5 5 0 0 0 7.1.1l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1"/><path d="M14 11a5 5 0 0 0-7.1-.1l-2 2a5 5 0 0 0 7.1 7.1l1.1-1.1"/>',
        'twitter' => '<path d="M5 19l6.2-7.1L5.4 5H9l3.3 4.4L16.2 5H19l-5.7 6.6L19.2 19H15.6l-3.6-4.8L8 19H5z"/>',
        'facebook' => '<path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.6l.4-3H13v-2c0-.6.4-1 1-1z"/>',
        'linkedin' => '<path d="M6 9H3v12h3zM4.5 3A1.8 1.8 0 1 0 4.5 6.6 1.8 1.8 0 0 0 4.5 3zM21 13.5c0-2.6-1.4-4.5-4-4.5a3.5 3.5 0 0 0-3 1.6V9H11v12h3v-6.2c0-1.5.8-2.4 2-2.4s1.8.8 1.8 2.4V21H21z"/>',
        'instagram' => '<rect x="4" y="4" width="16" height="16" rx="4"/><circle cx="12" cy="12" r="3.5"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"/>',
        'youtube' => '<path d="M21 12.2s0-3.1-.4-4.4a2.6 2.6 0 0 0-1.8-1.8C17.3 5.6 12 5.6 12 5.6s-5.3 0-6.8.4a2.6 2.6 0 0 0-1.8 1.8C3 9.1 3 12.2 3 12.2s0 3.1.4 4.4a2.6 2.6 0 0 0 1.8 1.8c1.5.4 6.8.4 6.8.4s5.3 0 6.8-.4a2.6 2.6 0 0 0 1.8-1.8c.4-1.3.4-4.4.4-4.4z"/><path d="M10 15.2l5-3-5-3v6z" fill="currentColor" stroke="none"/>',
        'check' => '<path d="M5 12l5 5L20 7"/>',
        'logout' => '<path d="M10 7V5H5v14h5v-2"/><path d="M10 12h9M16 8l4 4-4 4"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'image' => '<rect x="4" y="5" width="16" height="14" rx="2"/><circle cx="9" cy="10" r="1.4"/><path d="M7 16l3.2-3.2a1 1 0 0 1 1.4 0L16 17"/>',
    ];
    $body = $icons[$name] ?? $icons['arrow'];
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

function send_security_headers(): void
{
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Permitted-Cross-Domain-Policies: none');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    $ga = setting('ga_id', '');
    $script = "'self'";
    $connect = "'self'";
    if ($ga !== '' && preg_match('/^(G|UA|GT)-[A-Z0-9-]+$/', $ga)) {
        $script .= ' https://www.googletagmanager.com';
        $connect .= ' https://www.google-analytics.com https://region1.google-analytics.com';
    }
    header(
        "Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; script-src {$script}; frame-src https://www.youtube.com https://www.youtube-nocookie.com; connect-src {$connect}; base-uri 'self'; form-action 'self'; object-src 'none'"
    );
}

function prepare_article(string $html): array
{
    $toc = [];
    $used = [];
    $html = preg_replace_callback('/<(h[23])([^>]*)>(.*?)<\/\1>/is', static function (array $match) use (&$toc, &$used): string {
        $text = trim(html_entity_decode(strip_tags($match[3]), ENT_QUOTES, 'UTF-8'));
        $id = slugify($text);
        $base = $id;
        $n = 2;
        while (isset($used[$id])) {
            $id = $base . '-' . $n;
            $n++;
        }
        $used[$id] = true;
        $toc[] = ['level' => strtolower($match[1]), 'id' => $id, 'text' => $text];
        return '<' . $match[1] . ' id="' . e($id) . '">' . $match[3] . '</' . $match[1] . '>';
    }, $html) ?? $html;
    return [$html, $toc];
}

function json_ld_script(array $data): string
{
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

function is_bot(?string $ua = null): bool
{
    $ua = strtolower($ua ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if ($ua === '') {
        return false;
    }
    foreach (['bot', 'crawl', 'spider', 'slurp', 'preview', 'wget', 'curl/'] as $needle) {
        if (str_contains($ua, $needle)) {
            return true;
        }
    }
    return false;
}

function selected($current, $value): string
{
    return (string) $current === (string) $value ? 'selected' : '';
}

function checked($condition): string
{
    return $condition ? 'checked' : '';
}

function media_src(?string $path): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return '/' . ltrim($path, '/');
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $letters = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $letters .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $letters !== '' ? $letters : 'M';
}

function social_href(string $settingKey, string $base): ?string
{
    $value = trim(setting($settingKey, ''));
    if ($value === '') {
        return null;
    }
    if ($safe = safe_url($value)) {
        return $safe;
    }
    $handle = ltrim($value, '@');
    if (!preg_match('/^[A-Za-z0-9_\.]{2,80}$/', $handle)) {
        return null;
    }
    return $base . rawurlencode($handle);
}
