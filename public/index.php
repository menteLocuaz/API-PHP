<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../config/app.php';

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php.log');
error_reporting(E_ALL);

use Arancamon\ApiPhp\Controllers\RoutesController;
use Arancamon\ApiPhp\Models\Connection;

/*=============================================
 * CORS
 * =============================================*/

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('content-type: application/json; charset=utf-8');

// COnexion
Connection::connect();

$router = new RoutesController();
$router->index();
