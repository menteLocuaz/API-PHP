<?php

require __DIR__ . '/../vendor/autoload.php';

use OpenApi\Generator;

try {
    $openapi = (new Generator())->generate([
        __DIR__ . '/../app/',
    ]);

    $outputDir = __DIR__ . '/../public/swagger/';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    $json = $openapi->toJson();
    file_put_contents($outputDir . 'openapi.json', $json);

    header('Content-Type: application/json');
    echo $json;
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit(1);
}
