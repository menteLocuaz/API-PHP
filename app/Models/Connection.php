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
