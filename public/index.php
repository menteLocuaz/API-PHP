<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../config/app.php';

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php.log');
error_reporting(E_ALL);

use Arancamon\ApiPhp\Controllers\RoutesController;
use Arancamon\ApiPhp\Models\Connection;

echo '<pre>';
print_r(Connection::Connect());
echo '</pre>';

$router = new RoutesController();
$router->index();
