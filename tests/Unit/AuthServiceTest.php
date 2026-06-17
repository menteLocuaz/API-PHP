<?php

declare(strict_types=1);

use Arancamon\ApiPhp\Security\AuthService;

beforeEach(function () {
    unset($_ENV['API_KEY']);
});

test('apiKey returns empty string when no env var set', function () {
    $key = AuthService::apiKey();

    expect($key)->toBe('');
});

test('apiKey returns env value when set', function () {
    $_ENV['API_KEY'] = 'sk-abc123';

    $key = AuthService::apiKey();

    expect($key)->toBe('sk-abc123');
});

test('publicAccess returns array with empty string', function () {
    expect(AuthService::publicAccess())->toBe(['']);
});
