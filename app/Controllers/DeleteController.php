<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Http\Response;
use Arancamon\ApiPhp\Models\DeleteModel;

class DeleteController
{
    public static function deleteData(string $table, mixed $id, string $nameId): void
    {
        $response = DeleteModel::deleteData($table, $id, $nameId);
        self::response($response);
    }

    private static function response(?array $response): void
    {
        if (!empty($response)) {
            Response::json($response);
        } else {
            Response::notFound('delete');
        }
    }
}
