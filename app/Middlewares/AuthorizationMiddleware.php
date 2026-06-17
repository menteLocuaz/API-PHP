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
        $authorization = Request::header('Authorization');

        if ($authorization === AuthService::apiKey()) {
            return true;
        }

        if (in_array($table, AuthService::publicAccess(), true)) {
            GetController::find($table, '*', null, null, null, null);

            return false;
        }

        Response::error('You are not authorized to make this request');

        return false;
    }
}
