<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Models;

use Arancamon\ApiPhp\Database\Connection;
use Arancamon\ApiPhp\Models\Connection as ModelsConnection;
use PDO;
use PDOException;

class PosModel
{
    public static function postData(string $table, array $data): array
    {
        if (empty($data)) {
            return [
                'status' => 400,
                'results' => 'Error: No data provided',
            ];
        }

        $columns = array_keys($data);

        if (empty(ModelsConnection::getColumnsData($table, $columns))) {
            return [
                'status' => 400,
                'results' => 'Error: Fields in the form do not match the database',
            ];
        }

        $columnList = implode(',', $columns);
        $paramList = ':' . implode(',:', $columns);

        $sql = "INSERT INTO {$table} ({$columnList}) VALUES ({$paramList})";

        $link = Connection::connect();
        $stmt = $link->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value, PDO::PARAM_STR);
        }

        try {
            $stmt->execute();

            return [
                'lastId' => (int) $link->lastInsertId(),
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
