<?php

declare(strict_types=1);

use Arancamon\ApiPhp\Database\Connection;

beforeEach(function () {
    unset($_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
    Connection::reset();
});

test('getConfig throws when DB_HOST not set', function () {
    Connection::getConfig();
})->throws(RuntimeException::class, 'DB_HOST no configurado');

test('getConfig returns env values when set', function () {
    $_ENV['DB_HOST'] = '10.0.0.1';
    $_ENV['DB_PORT'] = '5433';
    $_ENV['DB_NAME'] = 'testdb';
    $_ENV['DB_USER'] = 'admin';
    $_ENV['DB_PASS'] = 'secret';

    $info = Connection::getConfig();

    expect($info)->toBe([
        'host' => '10.0.0.1',
        'port' => '5433',
        'database' => 'testdb',
        'username' => 'admin',
        'password' => 'secret',
    ]);
});

test('getConfig uses defaults for optional fields', function () {
    $_ENV['DB_HOST'] = '192.168.1.1';

    $info = Connection::getConfig();

    expect($info['host'])->toBe('192.168.1.1');
    expect($info['port'])->toBe('5432');
    expect($info['database'])->toBe('');
    expect($info['username'])->toBe('');
    expect($info['password'])->toBe('');
});


