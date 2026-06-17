<?php

$isDebug = ($_ENV['APP_DEBUG'] ?? false);

ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php.log');
error_reporting(E_ALL);
