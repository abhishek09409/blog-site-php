<?php

declare(strict_types=1);

class CommentAdmin
{
    public function index(): void
    {
        $user = admin_require('comments.moderate', 'comments.own');
        $status = (string) ($_GET['status'] ?? 'pending');
        if (!in_array($status, ['all', 'pending', 'approved', 'rejected', 'spam'], true)) {
            $status = 'pending';
        }
        admin_view('comments', [
            'title' => 'Comments',
            'comments' => Comment::adminList($status, !Auth::can('comments.moderate'), (int) $user['id']),
            'status' => $status,
            'canModerate' => Auth::can('comments.moderate'),
        ]);
    }

    public function status(array $params): void
    {
        admin_require('comments.moderate');
        Csrf::verify();
        $status = (string) ($_POST['status'] ?? '');
        if (!in_array($status, ['pending', 'approved', 'rejected', 'spam'], true)) {
            throw new HttpException('Unknown comment status.', 422);
        }
        $comment = Comment::find((int) $params['id']);
        if (!$comment) {
            throw new HttpException('Comment not found.', 404);
        }
        Comment::setStatus((int) $comment['id'], $status);
        ActivityLog::record('comment.' . $status, 'Marked a comment as ' . $status);
        flash('success', 'Comment updated.');
        redirect('/admin/comments?status=' . $status);
    }

    public function delete(array $params): void
    {
        $user = admin_require('comments.moderate', 'comments.own');
        Csrf::verify();
        $comment = Comment::find((int) $params['id']);
        if (!$comment) {
            throw new HttpException('Comment not found.', 404);
        }
        if (!Auth::can('comments.moderate')) {
            $post = Post::find((int) $comment['post_id']);
            if (!$post || (int) $post['author_id'] !== (int) $user['id']) {
                throw new HttpException('You cannot delete that comment.', 403);
            }
        }
        Comment::delete((int) $comment['id']);
        flash('success', 'Comment deleted.');
        redirect('/admin/comments');
    }
}
