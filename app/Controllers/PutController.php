<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Models\PutModel;

class PutController
{
    public static function putData(string $table, array $data, mixed $id, string $nameId): void
    {
        $response = PutModel::putData($table, $data, $id, $nameId);

        self::response($response);
    }

    private static function response(array $response): void
    {
        if (!empty($response)) {
            $status = 200;
            $json = [
                'status' => $status,
                'results' => $response,
            ];
        } else {
            $status = 404;
            $json = [
                'status' => $status,
                'results' => 'Not Found',
                'method' => 'put',
            ];
        }

        http_response_code($status);

        header('Content-Type: application/json');

        echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
