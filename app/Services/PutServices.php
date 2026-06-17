<?php

use Arancamon\ApiPhp\Controllers\PutController;
use Arancamon\ApiPhp\Models\Connection as ModelsConnection;
use Arancamon\ApiPhp\Security\TokenStatus;

$table = explode('?', $routesArray[1])[0];

if (isset($_GET['id']) && isset($_GET['nameId'])) {
    $data = [];

    parse_str(file_get_contents('php://input'), $data);

    $columns = array_keys($data);
    $columns[] = $_GET['nameId'];
    $columns = array_unique($columns);

    if (empty(ModelsConnection::getColumnsData($table, $columns))) {
        $json = [
            'status' => 400,
            'results' => 'Error: Fields in the form do not match the database',
        ];

        http_response_code($json['status']);
        echo json_encode($json);

        return;
    }

    if (isset($_GET['token'])) {
        if ($_GET['token'] == 'no' && isset($_GET['except'])) {
            $columns = [$_GET['except']];

            if (empty(ModelsConnection::getColumnsData($table, $columns))) {
                $json = [
                    'status' => 400,
                    'results' => 'Error: Fields in the form do not match the database',
                ];

                http_response_code($json['status']);
                echo json_encode($json);

                return;
            }

            PutController::putData($table, $data, $_GET['id'], $_GET['nameId']);
        } else {
            $tableToken = $_GET['table'] ?? 'users';
            $suffix = $_GET['suffix'] ?? 'user';

            $validate = ModelsConnection::tokenValidate($_GET['token'], $tableToken, $suffix);

            if ($validate === TokenStatus::VALID) {
                PutController::putData($table, $data, $_GET['id'], $_GET['nameId']);
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
