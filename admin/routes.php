<?php

declare(strict_types=1);

/** @var Router $router */

$router->get('/', static fn () => (new DashboardAdmin())->index());
$router->get('/posts', static fn () => (new PostAdmin())->index());
$router->get('/posts/create', static fn () => (new PostAdmin())->create());
$router->post('/posts/create', static fn () => (new PostAdmin())->store());
$router->get('/posts/edit/{id}', static fn (array $params) => (new PostAdmin())->edit($params));
$router->post('/posts/edit/{id}', static fn (array $params) => (new PostAdmin())->update($params));
$router->post('/posts/delete/{id}', static fn (array $params) => (new PostAdmin())->delete($params));
$router->post('/posts/duplicate/{id}', static fn (array $params) => (new PostAdmin())->duplicate($params));

$router->get('/categories', static fn () => (new CategoryAdmin())->index());
$router->post('/categories', static fn () => (new CategoryAdmin())->save());
$router->post('/categories/delete/{id}', static fn (array $params) => (new CategoryAdmin())->delete($params));

$router->get('/tags', static fn () => (new TagAdmin())->index());
$router->post('/tags', static fn () => (new TagAdmin())->save());
$router->post('/tags/delete/{id}', static fn (array $params) => (new TagAdmin())->delete($params));

$router->get('/media', static fn () => (new MediaAdmin())->index());
$router->post('/media', static fn () => (new MediaAdmin())->upload());
$router->post('/media/alt/{id}', static fn (array $params) => (new MediaAdmin())->alt($params));
$router->post('/media/delete/{id}', static fn (array $params) => (new MediaAdmin())->delete($params));

$router->get('/comments', static fn () => (new CommentAdmin())->index());
$router->post('/comments/status/{id}', static fn (array $params) => (new CommentAdmin())->status($params));
$router->post('/comments/delete/{id}', static fn (array $params) => (new CommentAdmin())->delete($params));

$router->get('/users', static fn () => (new UserAdmin())->index());
$router->get('/users/create', static fn () => (new UserAdmin())->create());
$router->post('/users/create', static fn () => (new UserAdmin())->store());
$router->get('/users/edit/{id}', static fn (array $params) => (new UserAdmin())->edit($params));
$router->post('/users/edit/{id}', static fn (array $params) => (new UserAdmin())->update($params));
$router->post('/users/delete/{id}', static fn (array $params) => (new UserAdmin())->delete($params));

$router->get('/newsletter', static fn () => (new NewsletterAdmin())->index());
$router->post('/newsletter/status/{id}', static fn (array $params) => (new NewsletterAdmin())->status($params));
$router->post('/newsletter/delete/{id}', static fn (array $params) => (new NewsletterAdmin())->delete($params));
$router->get('/newsletter/export', static fn () => (new NewsletterAdmin())->export());

$router->get('/messages', static fn () => (new MessageAdmin())->index());
$router->get('/messages/read/{id}', static fn (array $params) => (new MessageAdmin())->read($params));
$router->post('/messages/delete/{id}', static fn (array $params) => (new MessageAdmin())->delete($params));

$router->get('/menus', static fn () => (new MenuAdmin())->index());
$router->post('/menus', static fn () => (new MenuAdmin())->save());
$router->post('/menus/delete/{id}', static fn (array $params) => (new MenuAdmin())->delete($params));

$router->get('/settings', static fn () => (new SettingAdmin())->index());
$router->post('/settings', static fn () => (new SettingAdmin())->save());
$router->get('/profile', static fn () => (new ProfileAdmin())->index());
$router->post('/profile', static fn () => (new ProfileAdmin())->save());
