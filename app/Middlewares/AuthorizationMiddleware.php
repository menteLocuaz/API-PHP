<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Middlewares;

use Arancamon\ApiPhp\Controllers\GetController;
use Arancamon\ApiPhp\Http\Request;
use Arancamon\ApiPhp\Http\Response;
use Arancamon\ApiPhp\Security\AuthService;

class AuthorizationMiddleware
{
    public function handle(string $table): bool
    {
        $authorization = trim((string) Request::header('Authorization'));

        if (str_starts_with($authorization, 'Bearer ')) {
            $authorization = substr($authorization, 7);
        }

        if (hash_equals(AuthService::apiKey(), $authorization)) {
            return true;
        }

        if (in_array($table, AuthService::publicAccess(), true)) {
            (new GetController())->find($table, '*', null, null, null, null);

            return false;
        }

        Response::error('You are not authorized to make this request');

        return false;
    }
}
