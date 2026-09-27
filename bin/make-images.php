<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$serif = '/usr/share/fonts/truetype/noto/NotoSerif-Bold.ttf';
$sans = '/usr/share/fonts/truetype/macos/Inter-SemiBold.ttf';
$regular = '/usr/share/fonts/truetype/macos/Inter-Regular.ttf';

function hex(string $color): array
{
    $color = ltrim($color, '#');
    return [hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2))];
}

function cover(string $path, string $a, string $b, string $label): void
{
    global $sans, $regular;
    $w = 1600;
    $h = 1000;
    $im = imagecreatetruecolor($w, $h);
    imagealphablending($im, true);
    [$ar, $ag, $ab] = hex($a);
    [$br, $bg, $bb] = hex($b);
    for ($y = 0; $y < $h; $y++) {
        $t = $y / $h;
        $col = imagecolorallocate($im, (int) ($ar * (1 - $t) + $br * $t), (int) ($ag * (1 - $t) + $bg * $t), (int) ($ab * (1 - $t) + $bb * $t));
        imageline($im, 0, $y, $w, $y, $col);
    }
    $accent = imagecolorallocatealpha($im, 240, 90, 42, 40);
    imagefilledellipse($im, 1280, 180, 520, 520, $accent);
    imagefilledrectangle($im, 80, 760, 280, 772, imagecolorallocate($im, 240, 90, 42));
    $white = imagecolorallocate($im, 255, 255, 255);
    imagettftext($im, 28, 0, 80, 820, $white, $sans, strtoupper($label));
    imagettftext($im, 22, 0, 80, 880, imagecolorallocate($im, 226, 232, 240), $regular, 'MERIDIAN');
    imagejpeg($im, $path, 86);
    imagedestroy($im);
}

function avatar(string $path, string $initials, string $bg): void
{
    global $sans;
    $im = imagecreatetruecolor(400, 400);
    [$r, $g, $b] = hex($bg);
    imagefilledrectangle($im, 0, 0, 400, 400, imagecolorallocate($im, $r, $g, $b));
    $white = imagecolorallocate($im, 255, 255, 255);
    $box = imagettfbbox(92, 0, $sans, $initials);
    $x = (int) ((400 - ($box[2] - $box[0])) / 2);
    $y = (int) ((400 + ($box[1] - $box[7])) / 2);
    imagettftext($im, 92, 0, $x, $y, $white, $sans, $initials);
    imagejpeg($im, $path, 90);
    imagedestroy($im);
}

$covers = [
    ['01', '#111827', '#7c2d12', 'Technology'],
    ['02', '#1f2937', '#F05A2A', 'Intelligence'],
    ['03', '#0f172a', '#334155', 'Search'],
    ['04', '#1e3a5f', '#F05A2A', 'Business'],
    ['05', '#111827', '#374151', 'Strategy'],
    ['06', '#14532d', '#166534', 'Finance'],
    ['07', '#052e16', '#F05A2A', 'Money'],
    ['08', '#7c2d12', '#fdba74', 'Lifestyle'],
    ['09', '#0f4c5c', '#083344', 'Travel'],
    ['10', '#134e4a', '#F05A2A', 'Places'],
    ['11', '#1e293b', '#334155', 'Work'],
    ['12', '#111827', '#fb923c', 'Code'],
    ['13', '#3f1d12', '#111827', 'Security'],
    ['14', '#4c1d95', '#F05A2A', 'Design'],
    ['15', '#9a3412', '#111827', 'Media'],
];
foreach ($covers as [$file, $a, $b, $label]) {
    cover($root . "/assets/images/covers/{$file}.jpg", $a, $b, $label);
}
$people = [
    'elena' => ['EV', '#F05A2A'],
    'marcus' => ['MH', '#1e3a5f'],
    'priya' => ['PR', '#7c2d12'],
    'jonah' => ['JE', '#14532d'],
    'amira' => ['AS', '#0f4c5c'],
];
foreach ($people as $name => [$initials, $color]) {
    avatar($root . "/assets/images/avatars/{$name}.jpg", $initials, $color);
}
cover($root . '/assets/images/og-default.jpg', '#111827', '#F05A2A', 'Meridian');
echo "images ok\n";
