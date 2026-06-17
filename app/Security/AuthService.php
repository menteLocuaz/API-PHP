<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Security;

use Arancamon\ApiPhp\Models\GetModel;

class AuthService
{
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
