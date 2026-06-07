<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Definir la función config si aún no existe
if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

// Array asociativo con configuraciones de la aplicación
$app = [
    'name' => config('APP_NAME', 'Mi aplicación'), // Clave corregida
];
