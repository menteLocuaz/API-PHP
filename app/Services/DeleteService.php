<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Services;

use Arancamon\ApiPhp\Controllers\DeleteController;
use Arancamon\ApiPhp\Database\Connection;
use Arancamon\ApiPhp\Security\AuthService;
use Arancamon\ApiPhp\Security\TokenStatus;

class DeleteService
{
    public function handle(string $table, array $getParams): void
    {
        if (!isset($getParams['id']) || !isset($getParams['nameId'])) {
            return;
        }

        $columns = [$getParams['nameId']];

        if (empty(Connection::getColumnsData($table, $columns))) {
            $this->errorResponse('Error: Fields in the form do not match the database');
            return;
        }

        $controller = new DeleteController();

        if (isset($getParams['token'])) {
            if ($getParams['token'] === 'no' && isset($getParams['except'])) {
                $columns = [$getParams['except']];

                if (empty(Connection::getColumnsData($table, $columns))) {
                    $this->errorResponse('Error: Fields in the form do not match the database');
                    return;
                }

                $controller->deleteData($table, $getParams['id'], $getParams['nameId']);
            } else {
                $tableToken = $getParams['table'] ?? 'users';
                $suffix = $getParams['suffix'] ?? 'user';

                $validate = AuthService::tokenValidate($getParams['token'], $tableToken, $suffix);

                match ($validate) {
                    TokenStatus::VALID => $controller->deleteData($table, $getParams['id'], $getParams['nameId']),
                    TokenStatus::EXPIRED => $this->errorResponse('Error: The token has expired', 303),
                    TokenStatus::INVALID => $this->errorResponse('Error: The user is not authorized'),
                };
            }
        } else {
            $this->errorResponse('Error: Authorization required');
        }
    }

    private function errorResponse(string $message, int $status = 400): void
    {
        $json = [
            'status' => $status,
            'results' => $message,
        ];

        http_response_code($status);
        echo json_encode($json);
    }
}
