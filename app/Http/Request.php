<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Http;

class Request
{
    private static ?string $body = null;
    private static ?array $parsedBody = null;
    private static ?array $headers = null;

    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public static function query(?string $key = null): mixed
    {
        if ($key !== null) {
            return $_GET[$key] ?? null;
        }

        return $_GET;
    }

    public static function headers(): array
    {
        if (self::$headers === null) {
            self::$headers = getallheaders();
        }

        return self::$headers;
    }

    public static function header(string $name): ?string
    {
        return self::headers()[$name] ?? null;
    }

    public static function body(): array
    {
        if (self::$parsedBody !== null) {
            return self::$parsedBody;
        }

        $raw = self::raw();

        if ($raw === '') {
            self::$parsedBody = [];

            return self::$parsedBody;
        }

        $contentType = self::header('Content-Type') ?? '';

        if (str_contains($contentType, 'application/json')) {
            self::$parsedBody = json_decode($raw, true) ?? [];
        } else {
            parse_str($raw, self::$parsedBody);
        }

        return self::$parsedBody;
    }

    public static function raw(): string
    {
        if (self::$body === null) {
            self::$body = file_get_contents('php://input') ?: '';
        }

        return self::$body;
    }

    public static function reset(): void
    {
        self::$body = null;
        self::$parsedBody = null;
        self::$headers = null;
    }
}
