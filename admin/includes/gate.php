<?php

declare(strict_types=1);

function admin_user(): array
{
    if (!Auth::check()) {
        $next = $_SERVER['REQUEST_URI'] ?? '/admin';
        redirect('/login?next=' . rawurlencode($next));
    }
    if (!Auth::can('dashboard')) {
        throw new HttpException('You do not have access to the newsroom.', 403);
    }
    return Auth::user();
}

function admin_require(string ...$permissions): array
{
    $user = admin_user();
    if (!Auth::canAny($permissions)) {
        throw new HttpException('You do not have permission for that action.', 403);
    }
    return $user;
}
