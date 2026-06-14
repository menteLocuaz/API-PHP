<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database;

use Arancamon\ApiPhp\Database\Contracts\ConnectionInterface;
use PDO;
use PDOException;

class Connection implements ConnectionInterface
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $db = self::getInfoDatabase();

        $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['database']}";

        try {
            self::$pdo = new PDO($dsn, $db['username'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            self::$pdo->exec("SET NAMES 'UTF8'");

            return self::$pdo;
        } catch (PDOException $e) {
            throw new PDOException(
                'Error al conectar a la base de datos: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e,
            );
        }
    }

    public static function execute(string $sql, array $params = []): array
    {
        $stmt = self::connect()->prepare($sql);

        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    public static function beginTransaction(): bool
    {
        return self::connect()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::connect()->commit();
    }

    public static function rollback(): bool
    {
        return self::connect()->rollBack();
    }

    public static function reset(): void
    {
        self::$pdo = null;
    }

    private static function getInfoDatabase(): array
    {
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'database' => $_ENV['DB_NAME'] ?? 'arctic',
            'username' => $_ENV['DB_USER'] ?? 'sa',
            'password' => $_ENV['DB_PASS'] ?? '52UYT',
        ];
    }
}
