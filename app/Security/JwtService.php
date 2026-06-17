<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Security;

class JwtService
{
    public static function jwt(
        int|string $id,
        string $email,
    ): array {
        $time = time();

        return [
            'iat' => $time,
            'exp' => $time + (60 * 60 * 24),
            'data' => [
                'id' => $id,
                'email' => $email,
            ],
        ];
    }
}
