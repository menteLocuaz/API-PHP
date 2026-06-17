<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Http;

class Response
{
    /**
     * Sends a consistent JSON response.
     *
     * @param mixed       $results The data to return.
     * @param int         $status  The HTTP status code.
     * @param string|null $error   Optional error message.
     * @param array       $extra   Optional extra fields to merge into the response.
     */
    public static function json(
        mixed $results,
        int $status = 200,
        ?string $error = null,
        array $extra = []
    ): void {
        $response = [
            'status' => $status,
            'results' => $error ?? $results,
        ];

        if ($status === 200 && (is_array($results) || $results instanceof \Countable)) {
            $response['total'] = count($results);
        }

        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }

        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Standardized "Not Found" response.
     */
    public static function notFound(?string $method = null): void
    {
        $extra = $method ? ['method' => $method] : [];
        self::json(null, 404, 'Not Found', $extra);
    }

    /**
     * Standardized "Error" response.
     */
    public static function error(string $message, int $status = 400): void
    {
        self::json(null, $status, $message);
    }
}
