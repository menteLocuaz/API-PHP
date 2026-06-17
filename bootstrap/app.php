<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$isDebug = ($_ENV['APP_DEBUG'] ?? false);

ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php.log');
error_reporting(E_ALL);

date_default_timezone_set('UTC');
