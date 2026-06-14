<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database;

class QueryOptions
{
    public function __construct(
        public readonly ?string $orderBy = null,
        public readonly ?string $orderMode = null,
        public readonly ?int $startAt = null,
        public readonly ?int $endAt = null,
    ) {}

    public function hasOrder(): bool
    {
        return $this->orderBy !== null && $this->orderMode !== null;
    }

    public function hasLimit(): bool
    {
        return $this->startAt !== null && $this->endAt !== null;
    }
}
