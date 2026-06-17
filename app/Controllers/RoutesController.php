<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Controllers;

final class RoutesController
{
    /**
     * Carga las rutas de la API.
     */
    public function index(): void
    {
        $routeFile = dirname(__DIR__) . '/Routes/api.php';

        if (!file_exists($routeFile)) {
            throw new \RuntimeException("The path file was not found: {$routeFile}");
        }

        require_once $routeFile;
    }
}

