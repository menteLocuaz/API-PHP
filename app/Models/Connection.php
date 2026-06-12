<?php

namespace Arancamon\ApiPhp\Models;

use PDO;
use PDOException;

class Connection
{
    /**
     * Información de conexión a la base de datos.
     *
     * @return array
     */
    public static function getInfoDatabase(): array
    {
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'database' => $_ENV['DB_NAME'] ?? 'arctic',
            'username' => $_ENV['DB_USER'] ?? 'sa',
            'password' => $_ENV['DB_PASS'] ?? '52UYT',
        ];
    }

    /**
     * Validar que las columnas existen en la tabla.
     *
     * @param string $table
     * @param array $columns
     * @return array
     */
    public static function getColumnsData(string $table, array $columns): array
    {
        if (empty($columns)) {
            return [];
        }

        $parts = explode('.', $table);
        if (count($parts) > 1) {
            $schema = $parts[0];
            $tableName = $parts[1];
        } else {
            $schema = 'public';
            $tableName = $table;
        }

        if ($columns === ['*']) {
            $sql = 'SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ? LIMIT 1';
            $stmt = self::Connect()->prepare($sql);
            $stmt->execute([$schema, $tableName]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = "SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name IN ($placeholders)";

        $stmt = self::Connect()->prepare($sql);
        $stmt->execute(array_merge([$schema, $tableName], $columns));

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Conexión a la base de datos mediante PDO.
     *
     * @return PDO
     * @throws PDOException
     */
    public static function Connect(): PDO
    {
        $db = self::getInfoDatabase();

        $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['database']}";

        try {
            $pdo = new PDO($dsn, $db['username'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Establecer codificación UTF-8
            $pdo->exec("SET NAMES 'UTF8'");

            return $pdo;
        } catch (PDOException $e) {
            throw new PDOException(
                'Error al conectar a la base de datos: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e,
            );
        }
    }
}
