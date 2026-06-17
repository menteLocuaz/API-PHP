<?php

declare(strict_types=1);

use Arancamon\ApiPhp\Models\Connection;

beforeEach(function () {
    unset($_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['API_KEY']);
});

// ─── databaseConfig ───────────────────────────────────────────────────────────

test('databaseConfig throws when DB_HOST not set', function () {
    Connection::databaseConfig();
})->throws(RuntimeException::class, 'DB_HOST no configurado');

test('databaseConfig returns env values when set', function () {
    $_ENV['DB_HOST'] = '10.0.0.1';
    $_ENV['DB_PORT'] = '5433';
    $_ENV['DB_NAME'] = 'testdb';
    $_ENV['DB_USER'] = 'admin';
    $_ENV['DB_PASS'] = 'secret';

    $info = Connection::databaseConfig();

    expect($info)->toBe([
        'host' => '10.0.0.1',
        'port' => '5433',
        'database' => 'testdb',
        'username' => 'admin',
        'password' => 'secret',
    ]);
});

test('databaseConfig uses defaults for optional fields', function () {
    $_ENV['DB_HOST'] = '192.168.1.1';

    $info = Connection::databaseConfig();

    expect($info['host'])->toBe('192.168.1.1');
    expect($info['port'])->toBe('5432');
    expect($info['database'])->toBe('');
    expect($info['username'])->toBe('');
    expect($info['password'])->toBe('');
});

// ─── apiKey ───────────────────────────────────────────────────────────────────

test('apiKey returns empty string when no env var set', function () {
    $key = Connection::apiKey();

    expect($key)->toBe('');
});

test('apiKey returns env value when set', function () {
    $_ENV['API_KEY'] = 'sk-abc123';

    $key = Connection::apiKey();

    expect($key)->toBe('sk-abc123');
});

// ─── publicAccess ─────────────────────────────────────────────────────────────

test('publicAccess returns array with empty string', function () {
    expect(Connection::publicAccess())->toBe(['']);
});

