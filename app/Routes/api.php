<?php

use Arancamon\ApiPhp\Controllers\GetController;
use Arancamon\ApiPhp\Http\Response;
use Arancamon\ApiPhp\Security\AuthService;
use Arancamon\ApiPhp\Services\DeleteService;
use Arancamon\ApiPhp\Services\GetService;
use Arancamon\ApiPhp\Services\PostService;
use Arancamon\ApiPhp\Services\PutService;

$routesArray = explode('/', $_SERVER['REQUEST_URI']);
$routesArray = array_filter($routesArray);

if (count($routesArray) == 0) {
    Response::notFound();
    return;
}

if (count($routesArray) == 1 && isset($_SERVER['REQUEST_METHOD'])) {
    $table = explode('?', $routesArray[1])[0];

    if (
        !isset(getallheaders()['Authorization'])
        || getallheaders()['Authorization'] != AuthService::apiKey()
    ) {
        if (in_array($table, AuthService::publicAccess()) == 0) {
            Response::error('You are not authorized to make this request');
            return;
        }

        GetController::find($table, '*', null, null, null, null);

        return;
    }

    $method = $_SERVER['REQUEST_METHOD'];

    match ($method) {
        'GET' => (new GetService())->handle($table, $_GET),
        'POST' => (new PostService())->handle(
            $table,
            $_POST ?: json_decode(file_get_contents('php://input'), true) ?? [],
            $_GET,
        ),
        'PUT' => (function () use ($table) {
            parse_str(file_get_contents('php://input'), $data);
            (new PutService())->handle($table, $data, $_GET);
        })(),
        'DELETE' => (new DeleteService())->handle($table, $_GET),
        default => Response::error('Method Not Allowed', 405),
    };
}
