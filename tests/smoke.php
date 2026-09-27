<?php

declare(strict_types=1);

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
$paths = [
    '/' => 200,
    '/blog' => 200,
    '/blog/page/2' => 200,
    '/blog/how-to-build-a-modern-php-website' => 200,
    '/category/technology' => 200,
    '/tag/php' => 200,
    '/author/elena' => 200,
    '/search?q=php' => 200,
    '/about' => 200,
    '/contact' => 200,
    '/privacy' => 200,
    '/terms' => 200,
    '/login' => 200,
    '/sitemap.xml' => 200,
    '/robots.txt' => 200,
    '/not-a-real-page' => 404,
];
$failed = 0;
foreach ($paths as $path => $expect) {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true]);
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $noise = is_string($raw) && preg_match('/(Fatal error|Warning:|Notice:)/', $raw);
    if ($code !== $expect || $noise) {
        $failed++;
        echo "FAIL {$code} {$path}\n";
    } else {
        echo "OK {$code} {$path}\n";
    }
}
exit($failed > 0 ? 1 : 0);
