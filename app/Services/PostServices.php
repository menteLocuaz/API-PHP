<?php

declare(strict_types=1);

use Arancamon\ApiPhp\Controllers\PosController;
use Arancamon\ApiPhp\Database\Connection;
use Arancamon\ApiPhp\Security\AuthService;
use Arancamon\ApiPhp\Security\TokenStatus;

$table = explode('?', $routesArray[1])[0];

if (isset($_POST) || file_get_contents('php://input') !== false) {
    $data = $_POST ?: json_decode(file_get_contents('php://input'), true) ?? [];

    $columns = array_keys($data);

    if (empty(Connection::getColumnsData($table, $columns))) {
        $json = [
            'status' => 400,
            'results' => 'Error: Fields in the form do not match the database',
        ];

        http_response_code($json['status']);
        echo json_encode($json);

        return;
    }

    $response = new PosController();

    if (isset($_GET['register']) && $_GET['register'] == true) {
        $suffix = $_GET['suffix'] ?? 'user';

        $response->postRegister($table, $data, $suffix);
    } elseif (isset($_GET['login']) && $_GET['login'] == true) {
        $suffix = $_GET['suffix'] ?? 'user';

        $response->postLogin($table, $data, $suffix);
    } else {
        if (isset($_GET['token'])) {
            if ($_GET['token'] == 'no' && isset($_GET['except'])) {
                $columns = [$_GET['except']];

                if (empty(Connection::getColumnsData($table, $columns))) {
                    $json = [
                        'status' => 400,
                        'results' => 'Error: Fields in the form do not match the database',
                    ];

                    http_response_code($json['status']);
                    echo json_encode($json);

                    return;
                }

                $response->postData($table, $data);
            } else {
                $tableToken = $_GET['table'] ?? 'users';
                $suffix = $_GET['suffix'] ?? 'user';

                $validate = AuthService::tokenValidate($_GET['token'], $tableToken, $suffix);

                if ($validate === TokenStatus::VALID) {
                    $response->postData($table, $data);
                }

                if ($validate === TokenStatus::EXPIRED) {
                    $json = [
                        'status' => 303,
                        'results' => 'Error: The token has expired',
                    ];

                    http_response_code($json['status']);
                    echo json_encode($json);

                    return;
                }

                if ($validate == 'no-auth') {
                    $json = [
                        'status' => 400,
                        'results' => 'Error: The user is not authorized',
                    ];

                    http_response_code($json['status']);
                    echo json_encode($json);

                    return;
                }
            }
        } else {
            $json = [
                'status' => 400,
                'results' => 'Error: Authorization required',
            ];

            http_response_code($json['status']);
            echo json_encode($json);

            return;
        }
    }
}
