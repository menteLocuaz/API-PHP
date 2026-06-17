<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Http\Response;
use Arancamon\ApiPhp\Models\PutModel;

class PutController
{
    public static function putData(string $table, array $data, mixed $id, string $nameId): void
    {
        $response = PutModel::putData($table, $data, $id, $nameId);
        self::response($response);
    }

    private static function response(?array $response): void
    {
        if (!empty($response)) {
            Response::json($response);
        } else {
            Response::notFound('put');
        }
    }
}
