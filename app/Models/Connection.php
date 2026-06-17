<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Models;

use PDO;
use PDOException;
use RuntimeException;
use Arancamon\ApiPhp\Database\Exceptions\DatabaseException;
use Arancamon\ApiPhp\Security\TokenStatus;

class Connection
{
    private static ?PDO $pdo = null;

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

    /**
     * Configuración de la base de datos desde variables de entorno.
     *
     * @return array{host: string, port: string, database: string, username: string, password: string}
     */
    public static function databaseConfig(): array
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
     * Clave API para autenticación de peticiones.
     */
    public static function apiKey(): string
    {
        return $_ENV['API_KEY'] ?? '';
    }

    /**
     * Tablas de acceso público que no requieren API key.
     */
    public static function publicAccess(): array
    {
        return [''];
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

    /**
     * Obtiene la conexión PDO reutilizable (Singleton).
     *
     * @throws DatabaseException Si no es posible conectar con PostgreSQL.
     */
    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $db = self::databaseConfig();

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
            throw new DatabaseException(
                'No fue posible conectar con PostgreSQL.',
                0,
                $e,
            );
        }
    }

    /**
     * Valida el estado de un token de autenticación.
     *
     * @param string $token  Token JWT a validar
     * @param string $table  Tabla donde buscar el token
     * @param string $suffix Sufijo del módulo (user, client, etc.)
     *
     * @return TokenStatus VALID si es válido, EXPIRED si expiró, INVALID si no existe
     */
    public static function tokenValidate(string $token, string $table, string $suffix): TokenStatus
    {
        $tokenColumn = "token_$suffix";
        $expireColumn = "token_exp_$suffix";

        $user = GetModel::findWithFilters(
            $table,
            $expireColumn,
            $tokenColumn,
            $token,
            null,
            null,
            null,
            null,
        );

        if (!empty($user)) {
            $time = time();

            if ($time < $user[0]->{$expireColumn}) {
                return TokenStatus::VALID;
            }

            return TokenStatus::EXPIRED;
        }

        return TokenStatus::INVALID;
    }
}
