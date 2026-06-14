<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database\Exceptions;

use PDOException;

class DatabaseException extends \RuntimeException
{
    public static function fromPDOException(PDOException $e): self
    {
        return new self('Database error: ' . $e->getMessage(), (int) $e->getCode(), $e);
    }
}
