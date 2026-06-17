<?php

use Arancamon\ApiPhp\Controllers\GetController;
use Arancamon\ApiPhp\Security\AuthService;

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

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        include __DIR__ . '/../Services/GetServices.php';
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        include __DIR__ . '/../Services/PostServices.php';
    }

    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        include __DIR__ . '/../Services/PutServices.php';
    }

    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        include __DIR__ . '/../Services/DeleteServices.php';
    }
}
