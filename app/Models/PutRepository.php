<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Models;

use Arancamon\ApiPhp\Database\Connection;
use Arancamon\ApiPhp\Database\Exceptions\DatabaseException;
use Arancamon\ApiPhp\Database\QueryBuilder;
use PDOException;

class PutRepository
{
    public function update(string $table, array $data, mixed $id, string $nameId): ?array
    {
        try {
            $sql = QueryBuilder::buildUpdate($table, array_keys($data), $nameId);
        } catch (\InvalidArgumentException $e) {
            return [
                'status' => 400,
                'results' => $e->getMessage(),
            ];
        }

        $link = Connection::connect();
        $stmt = $link->prepare($sql);

        $params = $data;
        $params[$nameId] = $id;

        try {
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return null;
            }

            return [
                'comment' => 'The process was successful',
            ];
        } catch (PDOException $e) {
            throw DatabaseException::fromPDOException($e);
        }
    }
}
