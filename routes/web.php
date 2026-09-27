<?php

declare(strict_types=1);

/** @var Router $router */

$router->get('/', static fn () => (new HomeController())->index());
$router->get('/blog', static fn () => (new BlogController())->index([]));
$router->get('/blog/page/{page}', static fn (array $params) => (new BlogController())->index($params));
$router->get('/blog/{slug}', static fn (array $params) => (new ArticleController())->show($params));
$router->post('/blog/{slug}/comments', static fn (array $params) => (new ArticleController())->comment($params));
$router->get('/preview/{id}', static fn (array $params) => (new ArticleController())->preview($params));

$router->get('/category/{slug}', static fn (array $params) => (new CategoryController())->show($params));
$router->get('/category/{slug}/page/{page}', static fn (array $params) => (new CategoryController())->show($params));
$router->get('/tag/{slug}', static fn (array $params) => (new TagController())->show($params));
$router->get('/tag/{slug}/page/{page}', static fn (array $params) => (new TagController())->show($params));
$router->get('/author/{slug}', static fn (array $params) => (new AuthorController())->show($params));
$router->get('/author/{slug}/page/{page}', static fn (array $params) => (new AuthorController())->show($params));

$router->get('/search', static fn () => (new SearchController())->index([]));
$router->get('/search/page/{page}', static fn (array $params) => (new SearchController())->index($params));

$router->get('/about', static fn () => (new PageController())->about());
$router->get('/contact', static fn () => (new PageController())->contact());
$router->post('/contact', static fn () => (new PageController())->sendContact());
$router->get('/privacy', static fn () => (new PageController())->privacy());
$router->get('/terms', static fn () => (new PageController())->terms());
$router->post('/newsletter', static fn () => (new PageController())->subscribe());

$router->get('/login', static fn () => (new AuthController())->loginForm());
$router->post('/login', static fn () => (new AuthController())->login());
$router->get('/register', static fn () => (new AuthController())->registerForm());
$router->post('/register', static fn () => (new AuthController())->register());
$router->post('/logout', static fn () => (new AuthController())->logout());
$router->get('/forgot-password', static fn () => (new AuthController())->forgotForm());
$router->post('/forgot-password', static fn () => (new AuthController())->forgot());
$router->get('/reset-password/{token}', static fn (array $params) => (new AuthController())->resetForm($params));
$router->post('/reset-password/{token}', static fn (array $params) => (new AuthController())->reset($params));
$router->get('/account', static fn () => (new AuthController())->account());
$router->post('/account', static fn () => (new AuthController())->updateAccount());

$router->get('/sitemap.xml', static fn () => (new SeoController())->sitemap());
$router->get('/robots.txt', static fn () => (new SeoController())->robots());
