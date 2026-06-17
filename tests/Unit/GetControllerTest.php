<?php

declare(strict_types=1);

use Arancamon\ApiPhp\Controllers\GetController;

test('response returns 404 JSON when response is empty', function () {
    $method = new ReflectionMethod(GetController::class, 'response');
    ob_start();
    $method->invoke(null, []);

    $output = ob_get_clean();
    $json = json_decode($output, true);

    expect($json)->toBe([
        'status' => 404,
        'results' => 'Not Found',
    ]);
});

test('response returns 200 JSON with results when response is not empty', function () {
    $method = new ReflectionMethod(GetController::class, 'response');

    $data = [['id' => 1, 'name' => 'Test']];

    ob_start();
    $method->invoke(null, $data);

    $output = ob_get_clean();
    $json = json_decode($output, true);

    expect($json)->toHaveKeys(['status', 'total', 'results']);
    expect($json['status'])->toBe(200);
    expect($json['total'])->toBe(1);
    expect($json['results'])->toBe($data);
});

test('response produces valid JSON', function () {
    $method = new ReflectionMethod(GetController::class, 'response');

    ob_start();
    $method->invoke(null, []);
    $output = ob_get_clean();

    expect(json_decode($output))->not->toBeNull();
    expect(json_last_error())->toBe(JSON_ERROR_NONE);
});
