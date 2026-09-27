<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
$_SERVER['HTTP_ACCEPT'] = 'application/json';
(new PageController())->subscribe();
