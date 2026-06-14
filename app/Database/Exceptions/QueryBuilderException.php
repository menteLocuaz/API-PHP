<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database\Exceptions;

class QueryBuilderException extends \InvalidArgumentException
{
    public static function invalidIdentifier(string $name): self
    {
        return new self("Invalid SQL identifier: {$name}");
    }

    public static function invalidOrderMode(string $mode): self
    {
        return new self("Invalid ORDER BY mode: {$mode}");
    }

    public static function tableNotFound(string $table): self
    {
        return new self("Table not found: {$table}");
    }
}
