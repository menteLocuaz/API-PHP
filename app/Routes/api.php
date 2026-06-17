<?php

use Arancamon\ApiPhp\Controllers\GetController;
use Arancamon\ApiPhp\Security\AuthService;
use Arancamon\ApiPhp\Services\DeleteService;
use Arancamon\ApiPhp\Services\GetService;
use Arancamon\ApiPhp\Services\PostService;
use Arancamon\ApiPhp\Services\PutService;

$routesArray = explode('/', $_SERVER['REQUEST_URI']);
$routesArray = array_filter($routesArray);

if (count($routesArray) == 0) {
    $json = [
        'status' => 404,
        'results' => 'Not Found',
    ];

    http_response_code($json['status']);
    echo json_encode($json);

    return;
}

if (count($routesArray) == 1 && isset($_SERVER['REQUEST_METHOD'])) {
    $table = explode('?', $routesArray[1])[0];

    if (
        !isset(getallheaders()['Authorization'])
        || getallheaders()['Authorization'] != AuthService::apiKey()
    ) {
        if (in_array($table, AuthService::publicAccess()) == 0) {
            $json = [
                'status' => 400,
                'results' => 'You are not authorized to make this request',
            ];

            http_response_code($json['status']);
            echo json_encode($json);

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
        default => (function () {
            http_response_code(405);
            echo json_encode(['status' => 405, 'results' => 'Method Not Allowed']);
        })(),
    };
}
