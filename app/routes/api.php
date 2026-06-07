<?php

// url actual

$routesArray = explode('/', $_SERVER['REQUEST_URI']);
$routesArray = array_filter($routesArray);

// echo '<pre>'; print_r($_SERVER['REQUEST_URI']); echo '</pre>';
// echo '<pre>'; print_r($routesArray); echo '</pre>';

if (count($routesArray) == 0) {
    $resp = array(
        'status' => 404,
        'result' => 'Not found',
    );

    echo json_encode($resp, http_response_code($resp['status']));

    return;
}
