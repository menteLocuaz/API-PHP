<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Models;

use Arancamon\ApiPhp\Database\Connection;
use PDO;
use PDOException;

class PutModel
{
    public static function putData(string $table, array $data, mixed $id, string $nameId): ?array
    {
        $response = GetModel::findWithFilters($table, $nameId, $nameId, $id, null, null, null, null);

        if (empty($response)) {
            return null;
        }

        $set = '';

        foreach ($data as $key => $value) {
            $set .= "{$key} = :{$key},";
        }

        $set = substr($set, 0, -1);

        $sql = "UPDATE {$table} SET {$set} WHERE {$nameId} = :{$nameId}";

        $link = Connection::connect();
        $stmt = $link->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $data[$key], PDO::PARAM_STR);
        }

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
