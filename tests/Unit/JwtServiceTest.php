<?php

declare(strict_types=1);

use Arancamon\ApiPhp\Security\JwtService;

test('jwt returns valid structure', function () {
    $token = JwtService::jwt(42, 'test@example.com');

    expect($token)->toHaveKeys(['iat', 'exp', 'data']);
    expect($token['iat'])->toBeInt();
    expect($token['exp'])->toBeInt();
    expect($token['exp'])->toBeGreaterThan($token['iat']);
    expect($token['data'])->toBe(['id' => 42, 'email' => 'test@example.com']);
});

test('jwt expiry is 24 hours after iat', function () {
    $token = JwtService::jwt(1, 'a@b.com');

    expect($token['exp'] - $token['iat'])->toBe(60 * 60 * 24);
});

test('jwt handles string id', function () {
    $token = JwtService::jwt('uuid-123', 'user@site.com');

    expect($token['data']['id'])->toBe('uuid-123');
});
