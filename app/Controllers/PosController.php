<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Models\PosModel;

class PosController
{
    public static function postData(string $table, array $data): void
    {
        $response = PosModel::postData($table, $data);

        self::response($response);
    }

    private static function response(array $response): void
    {
        if (isset($response['status'])) {
            $status = $response['status'];
            $json = [
                'status' => $status,
                'results' => $response['results'] ?? $response['comment'] ?? 'Error',
            ];
        } else {
            $status = 200;
            $json = [
                'status' => $status,
                'results' => $response,
            ];
        }

        http_response_code($status);

        header('Content-Type: application/json');

        echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
