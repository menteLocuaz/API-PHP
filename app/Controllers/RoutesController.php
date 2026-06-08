<?php

namespace Arancamon\ApiPhp\Controllers;

class RoutesController
{
    // ruta principal
    public function index()
    {
        include __DIR__ . '/../Routes/api.php';
    }
}

;
