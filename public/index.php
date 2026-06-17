<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/app.php';

use Arancamon\ApiPhp\Controllers\RoutesController;
use Arancamon\ApiPhp\Http\Cors;

Cors::send();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

(new RoutesController())->index();
