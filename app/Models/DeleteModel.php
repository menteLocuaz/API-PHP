<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Models;

use Arancamon\ApiPhp\Database\Connection;
use PDO;
use PDOException;

class DeleteModel
{
    public static function deleteData(string $table, mixed $id, string $nameId): ?array
    {
        $response = GetModel::findWithFilters($table, $nameId, $nameId, $id, null, null, null, null);

        if (empty($response)) {
            return null;
        }

        $sql = "DELETE FROM {$table} WHERE {$nameId} = :{$nameId}";

        $link = Connection::connect();
        $stmt = $link->prepare($sql);

        $stmt->bindValue(":{$nameId}", $id, PDO::PARAM_STR);

        try {
            $stmt->execute();

            return [
                'comment' => 'The process was successful',
            ];
        } catch (PDOException $e) {
            return [
                'status' => 500,
                'results' => $e->getMessage(),
            ];
        }
    }
}
