<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database\Contracts;

use PDO;

interface ConnectionInterface
{
    public static function connect(): PDO;

    public static function execute(string $sql, array $params = []): array;

    public static function beginTransaction(): bool;

    public static function commit(): bool;

    public static function rollback(): bool;
}
