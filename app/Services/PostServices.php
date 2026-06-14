<?php

use Arancamon\ApiPhp\Controllers\PosController;

$table = explode('?', $routesArray[0])[0];

$data = $_POST ?: json_decode(file_get_contents('php://input'), true) ?? $_GET ?: [];

PosController::postData($table, $data);

exit();
