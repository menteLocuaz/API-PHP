<?php

declare(strict_types=1);

use Arancamon\ApiPhp\Controllers\PutController;
use Arancamon\ApiPhp\Database\Exceptions\DatabaseException;
use Arancamon\ApiPhp\Models\PutRepository;

test('response returns 404 JSON when response is empty', function () {
    $controller = new PutController;
    $method = new ReflectionMethod(PutController::class, 'response');

    ob_start();
    $method->invoke($controller, null);

    $output = ob_get_clean();
    $json = json_decode($output, true);

    expect($json)->toBe([
        'status' => 404,
        'results' => 'Not Found',
        'method' => 'put',
    ]);
});

test('response returns 200 JSON with success comment when not empty', function () {
    $controller = new PutController;
    $method = new ReflectionMethod(PutController::class, 'response');

    $data = ['comment' => 'The process was successful'];

    ob_start();
    $method->invoke($controller, $data);

    $output = ob_get_clean();
    $json = json_decode($output, true);

    expect($json)->toHaveKeys(['status', 'results']);
    expect($json['status'])->toBe(200);
    expect($json['results'])->toBe($data);
});

test('response produces valid JSON', function () {
    $controller = new PutController;
    $method = new ReflectionMethod(PutController::class, 'response');

    ob_start();
    $method->invoke($controller, null);
    $output = ob_get_clean();

    expect(json_decode($output))->not->toBeNull();
    expect(json_last_error())->toBe(JSON_ERROR_NONE);
});

test('putData returns 404 when repository returns null', function () {
    $repository = new class extends PutRepository {
        public function update(string $table, array $data, mixed $id, string $nameId): ?array
        {
            return null;
        }
    };

    $controller = new PutController($repository);

    ob_start();
    $controller->putData('users', ['name' => 'test'], 1, 'id');
    $output = ob_get_clean();

    $json = json_decode($output, true);

    expect($json)->toBe([
        'status' => 404,
        'results' => 'Not Found',
        'method' => 'put',
    ]);
});

test('putData returns 200 on successful update', function () {
    $repository = new class extends PutRepository {
        public function update(string $table, array $data, mixed $id, string $nameId): ?array
        {
            return ['comment' => 'The process was successful'];
        }
    };

    $controller = new PutController($repository);

    ob_start();
    $controller->putData('users', ['name' => 'test'], 1, 'id');
    $output = ob_get_clean();

    $json = json_decode($output, true);

    expect($json)->toHaveKeys(['status', 'results']);
    expect($json['status'])->toBe(200);
    expect($json['results']['comment'])->toBe('The process was successful');
});

test('putData returns 500 on DatabaseException', function () {
    $repository = new class extends PutRepository {
        public function update(string $table, array $data, mixed $id, string $nameId): ?array
        {
            throw new DatabaseException('Database error: Connection failed');
        }
    };

    $controller = new PutController($repository);

    ob_start();
    $controller->putData('users', ['name' => 'test'], 1, 'id');
    $output = ob_get_clean();

    $json = json_decode($output, true);

    expect($json)->toBe([
        'status' => 500,
        'results' => 'Database error: Connection failed',
    ]);
});
