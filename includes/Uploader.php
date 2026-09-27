<?php

declare(strict_types=1);

class Uploader
{
    private const MIME = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public static function storeImage(array $file, ?int $userId, ?string $alt = null): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('Choose an image to upload.');
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('The upload did not complete.');
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > (int) config('app.upload_max')) {
            throw new RuntimeException('Images must be 5 MB or smaller.');
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('The upload could not be verified.');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        if (!isset(self::MIME[$mime])) {
            throw new RuntimeException('Upload a JPG, PNG, GIF, or WebP image.');
        }
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            throw new RuntimeException('That file extension is not allowed.');
        }
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmp),
            'image/png' => @imagecreatefrompng($tmp),
            'image/gif' => @imagecreatefromgif($tmp),
            'image/webp' => @imagecreatefromwebp($tmp),
            default => false,
        };
        if (!$image) {
            throw new RuntimeException('The file is not a valid image.');
        }
        $storedExt = self::MIME[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $storedExt;
        $relativeDir = 'uploads/' . date('Y/m');
        $absoluteDir = base_path($relativeDir);
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) {
            imagedestroy($image);
            throw new RuntimeException('Could not create the upload directory.');
        }
        $target = $absoluteDir . '/' . $filename;
        $saved = match ($mime) {
            'image/jpeg' => imagejpeg($image, $target, 86),
            'image/png' => imagepng($image, $target, 6),
            'image/gif' => imagegif($image, $target),
            'image/webp' => imagewebp($image, $target, 84),
            default => false,
        };
        imagedestroy($image);
        if (!$saved) {
            throw new RuntimeException('Could not store the image.');
        }
        chmod($target, 0644);
        $path = '/' . $relativeDir . '/' . $filename;
        $id = db()->insert('media', [
            'user_id' => $userId,
            'filename' => $filename,
            'original_name' => mb_substr((string) ($file['name'] ?? $filename), 0, 255),
            'filepath' => $path,
            'mime_type' => $mime,
            'size' => filesize($target) ?: $size,
            'alt_text' => $alt !== null && $alt !== '' ? mb_substr($alt, 0, 255) : null,
        ]);
        $row = db()->fetch('SELECT * FROM media WHERE id = ?', [$id]);
        if (!$row) {
            throw new RuntimeException('The image was stored but could not be indexed.');
        }
        return $row;
    }

    public static function deleteFile(string $filepath): void
    {
        $relative = ltrim($filepath, '/');
        if (!str_starts_with($relative, 'uploads/') || str_contains($relative, '..')) {
            return;
        }
        $uploads = realpath(base_path('uploads'));
        $full = realpath(base_path($relative));
        if ($uploads && $full && str_starts_with($full, $uploads) && is_file($full)) {
            unlink($full);
        }
    }
}
