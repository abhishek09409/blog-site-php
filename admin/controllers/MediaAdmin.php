<?php

declare(strict_types=1);

class MediaAdmin
{
    public function index(): void
    {
        $user = admin_require('media.view', 'media.own');
        $ownOnly = !Auth::can('media.view');
        admin_view('media', [
            'title' => 'Media library',
            'items' => Media::search(trim((string) ($_GET['q'] ?? '')), $ownOnly, (int) $user['id']),
            'q' => trim((string) ($_GET['q'] ?? '')),
        ]);
    }

    public function upload(): void
    {
        $user = admin_require('media.view', 'media.own');
        Csrf::verify();
        $files = $this->normalize($_FILES['images'] ?? []);
        if (!$files) {
            flash('error', 'Choose at least one image.');
            redirect('/admin/media');
        }
        $saved = 0;
        $errors = [];
        foreach ($files as $file) {
            try {
                Uploader::storeImage($file, (int) $user['id'], trim((string) ($_POST['alt_text'] ?? '')));
                $saved++;
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }
        if ($saved) {
            ActivityLog::record('media.upload', 'Uploaded ' . $saved . ' image' . ($saved === 1 ? '' : 's'));
            flash('success', $saved . ' image' . ($saved === 1 ? '' : 's') . ' uploaded.');
        }
        if ($errors) {
            flash('error', implode(' ', array_unique($errors)));
        }
        redirect('/admin/media');
    }

    public function alt(array $params): void
    {
        admin_require('media.view', 'media.own');
        Csrf::verify();
        $item = $this->owned((int) $params['id']);
        Media::updateAlt((int) $item['id'], trim((string) ($_POST['alt_text'] ?? '')));
        flash('success', 'Alt text saved.');
        redirect('/admin/media');
    }

    public function delete(array $params): void
    {
        admin_require('media.view', 'media.own');
        Csrf::verify();
        $item = $this->owned((int) $params['id']);
        Media::delete((int) $item['id']);
        ActivityLog::record('media.delete', 'Deleted ' . $item['original_name']);
        flash('success', 'Image deleted.');
        redirect('/admin/media');
    }

    private function owned(int $id): array
    {
        $item = Media::find($id);
        if (!$item) {
            throw new HttpException('Image not found.', 404);
        }
        if (!Auth::can('media.view') && (int) $item['user_id'] !== Auth::id()) {
            throw new HttpException('You can only edit your own images.', 403);
        }
        return $item;
    }

    private function normalize(array $files): array
    {
        if (!isset($files['name'])) {
            return [];
        }
        if (!is_array($files['name'])) {
            return [$files];
        }
        $normalized = [];
        foreach ($files['name'] as $index => $name) {
            if ($name === '') {
                continue;
            }
            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
            ];
        }
        return $normalized;
    }
}
