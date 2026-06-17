<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Services;

use Arancamon\ApiPhp\Controllers\PosController;
use Arancamon\ApiPhp\Database\Connection;
use Arancamon\ApiPhp\Http\Response;
use Arancamon\ApiPhp\Security\AuthService;
use Arancamon\ApiPhp\Security\TokenStatus;

class PostService
{
    public function handle(string $table, array $data, array $getParams): void
    {
        $columns = array_keys($data);

        if (empty(Connection::getColumnsData($table, $columns))) {
            Response::error('Error: Fields in the form do not match the database');
            return;
        }

        $controller = new PosController();

        if (isset($getParams['register']) && $getParams['register'] == 'true') {
            $suffix = $getParams['suffix'] ?? 'user';
            $controller->postRegister($table, $data, $suffix);
            return;
        }

        if (isset($getParams['login']) && $getParams['login'] == 'true') {
            $suffix = $getParams['suffix'] ?? 'user';
            $controller->postLogin($table, $data, $suffix);
            return;
        }

        if (isset($getParams['token'])) {
            if ($getParams['token'] === 'no' && isset($getParams['except'])) {
                $columns = [$getParams['except']];

                if (empty(Connection::getColumnsData($table, $columns))) {
                    Response::error('Error: Fields in the form do not match the database');
                    return;
                }

                $controller->postData($table, $data);
            } else {
                $tableToken = $getParams['table'] ?? 'users';
                $suffix = $getParams['suffix'] ?? 'user';

                $validate = AuthService::tokenValidate($getParams['token'], $tableToken, $suffix);

                match ($validate) {
                    TokenStatus::VALID => $controller->postData($table, $data),
                    TokenStatus::EXPIRED => Response::error('Error: The token has expired', 303),
                    TokenStatus::INVALID => Response::error('Error: The user is not authorized'),
                };
            }
        } else {
            Response::error('Error: Authorization required');
        }
    }
}
