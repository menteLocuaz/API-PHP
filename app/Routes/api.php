<?php

// Parsear la URL solicitada
$uri = trim($_SERVER['REQUEST_URI'], '/');
$routesArray = explode('/', $uri);
$routesArray = array_filter($routesArray);

// Si no se indica ninguna ruta
if (empty($routesArray)) {
    $response = [
        'status' => 404,
        'message' => 'Not Found',
    ];
    http_response_code($response['status']);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Si hay una ruta principal y se ha enviado un método HTTP
if (count($routesArray) === 1 && isset($_SERVER['REQUEST_METHOD'])) {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            require __DIR__ . '/../Services/GetServices.php';
            break;

        case 'POST':
            require __DIR__ . '/../Services/PostServices.php';
            break;

        case 'PUT':
            $response = ['status' => 200, 'result' => 'PUT'];
            break;

        case 'DELETE':
            $response = ['status' => 200, 'result' => 'DELETE'];
            break;

        case 'PATCH':
            $response = ['status' => 200, 'result' => 'PATCH'];
            break;

        default:
            $response = ['status' => 405, 'message' => 'Method Not Allowed'];
            break;
    }

    // Establecer encabezados y enviar respuesta
    http_response_code($response['status']);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Ruta desconocida o no implementada
$response = [
    'status' => 404,
    'message' => 'Endpoint not found',
];
http_response_code($response['status']);
header('Content-Type: application/json');
echo json_encode($response);
exit();
