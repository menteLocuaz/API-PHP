<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../config/app.php';

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php.log');
error_reporting(E_ALL);

// Requerimientos

use Arancamon\ApiPhp\Controllers\RoutesController;

$router = new RoutesController();
$router->index();
