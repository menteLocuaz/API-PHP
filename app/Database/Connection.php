<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database;

use Arancamon\ApiPhp\Database\Contracts\ConnectionInterface;
use PDO;
use PDOException;
use RuntimeException;

class Connection implements ConnectionInterface
{
    private static ?PDO $pdo = null;

    /**
     * Obtiene la conexión PDO reutilizable (Singleton).
     */
    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $db = self::getConfig();

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
            self::$pdo = null;
            throw new PDOException(
                'Error al conectar a la base de datos: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Ejecuta una consulta SQL y devuelve los resultados.
     */
    public static function execute(string $sql, array $params = []): array
    {
        $stmt = self::connect()->prepare($sql);

        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    /**
     * Recupera los nombres de columna válidos de una tabla.
     * Cuando $columns es ['*'] devuelve todas las columnas ordenadas por ordinal_position.
     *
     * @param string   $table   Nombre de la tabla (opcionalmente con esquema)
     * @param string[] $columns Lista de columnas a validar
     *
     * @return string[]
     */
    public static function getColumnsData(string $table, array $columns): array
    {
        if (empty($columns)) {
            return [];
        }

        [$schema, $table] = self::parseTable($table);

        $pdo = self::connect();

        if ($columns === ['*']) {
            $sql = 'SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ? ORDER BY ordinal_position';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$schema, $table]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = "SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name IN ($placeholders)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge([$schema, $table], $columns));

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
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

    /**
     * Configuración de la base de datos desde variables de entorno.
     */
    public static function getConfig(): array
    {
        return [
            'host' => $_ENV['DB_HOST'] ?? throw new RuntimeException('DB_HOST no configurado'),
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'database' => $_ENV['DB_NAME'] ?? '',
            'username' => $_ENV['DB_USER'] ?? '',
            'password' => $_ENV['DB_PASS'] ?? '',
        ];
    }

    /**
     * Divide una tabla con esquema (schema.table) en [schema, table].
     * Si no tiene esquema, asume 'public'.
     *
     * @return array{0: string, 1: string}
     */
    private static function parseTable(string $table): array
    {
        return str_contains($table, '.')
            ? explode('.', $table, 2)
            : ['public', $table];
    }
}
