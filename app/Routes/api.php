<?php

use Arancamon\ApiPhp\Http\Request;
use Arancamon\ApiPhp\Http\Response;
use Arancamon\ApiPhp\Middlewares\AuthorizationMiddleware;
use Arancamon\ApiPhp\Middlewares\RateLimiterMiddleware;
use Arancamon\ApiPhp\Services\DeleteService;
use Arancamon\ApiPhp\Services\GetService;
use Arancamon\ApiPhp\Services\PostService;
use Arancamon\ApiPhp\Services\PutService;

$limiter = new RateLimiterMiddleware(
    maxRequests: (int) ($_ENV['RATE_LIMIT_MAX'] ?? 60),
    windowSeconds: (int) ($_ENV['RATE_LIMIT_WINDOW'] ?? 60),
);

if (!$limiter->handle()) {
    return;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routes = array_values(array_filter(explode('/', $path)));

if (count($routes) === 0) {
    Response::notFound();
    return;
}

if (count($routes) === 1 && isset($_SERVER['REQUEST_METHOD'])) {
    $table = $routes[0];

    if (!(new AuthorizationMiddleware())->handle($table)) {
        return;
    }

    $method = Request::method();

    match ($method) {
        'GET' => (new GetService())->handle($table, Request::query()),
        'POST' => (new PostService())->handle($table, Request::body(), Request::query()),
        'PUT' => (new PutService())->handle($table, Request::body(), Request::query()),
        'DELETE' => (new DeleteService())->handle($table, Request::query()),
        default => Response::error('Method Not Allowed', 405),
    };
}
