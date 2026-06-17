<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Middlewares;

use Arancamon\ApiPhp\Http\Response;

class RateLimiterMiddleware
{
    private string $storageDir;
    private int $maxRequests;
    private int $windowSeconds;

    public function __construct(
        int $maxRequests = 60,
        int $windowSeconds = 60,
        ?string $storageDir = null,
    ) {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->storageDir = $storageDir ?? dirname(__DIR__, 2) . '/storage/rate-limits';
    }

    public function handle(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = hash('sha256', $ip);
        $file = $this->storageDir . '/' . $key . '.json';

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }

        $now = time();
        $data = $this->read($file);

        if ($data === null || ($now - $data['window_start']) > $this->windowSeconds) {
            $data = [
                'window_start' => $now,
                'count' => 0,
            ];
        }

        $data['count']++;

        $this->write($file, $data);

        $remaining = max(0, $this->maxRequests - $data['count']);

        header('X-RateLimit-Limit: ' . $this->maxRequests);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . ($data['window_start'] + $this->windowSeconds));

        if ($data['count'] > $this->maxRequests) {
            $retryAfter = ($data['window_start'] + $this->windowSeconds) - $now;
            header('Retry-After: ' . $retryAfter);
            Response::error('Too Many Requests', 429);

            return false;
        }

        if (mt_rand(1, 100) <= 5) {
            $this->garbageCollect();
        }

        return true;
    }

    private function read(string $file): ?array
    {
        if (!file_exists($file)) {
            return null;
        }

        $content = @file_get_contents($file);

        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);

        if (!is_array($data) || !isset($data['window_start'], $data['count'])) {
            return null;
        }

        return $data;
    }

    private function write(string $file, array $data): void
    {
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    private function garbageCollect(): void
    {
        $now = time();

        foreach (glob($this->storageDir . '/*.json') as $file) {
            $content = @file_get_contents($file);

            if ($content === false) {
                continue;
            }

            $data = json_decode($content, true);

            if (!is_array($data) || !isset($data['window_start'])) {
                @unlink($file);
                continue;
            }

            if (($now - $data['window_start']) > $this->windowSeconds * 2) {
                @unlink($file);
            }
        }
    }
}
