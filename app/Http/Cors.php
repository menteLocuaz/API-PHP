<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Http;

class Cors
{
    public static function send(): void
    {
        $origin = $_ENV['APP_CORS'] ?? '*';

        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Content-Type: application/json; charset=UTF-8');
    }
}
