<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database;

use Arancamon\ApiPhp\Database\Builders\JoinBuilder;
use Arancamon\ApiPhp\Database\Builders\SelectBuilder;
use Arancamon\ApiPhp\Database\Builders\WhereBuilder;

class QueryBuilder
{
    public static function buildSelect(string $table, string $select): string
    {
        return SelectBuilder::build($table, $select);
    }

    public static function buildWhere(array $conditions): string
    {
        return WhereBuilder::build($conditions);
    }

    public static function buildOrder(?string $orderBy, ?string $orderMode): string
    {
        return SelectBuilder::buildOrder($orderBy, $orderMode);
    }

    public static function buildLimit(?int $startAt, ?int $endAt): string
    {
        return SelectBuilder::buildLimit($startAt, $endAt);
    }

    public static function buildJoin(array $tables, array $types): string
    {
        return JoinBuilder::build($tables, $types);
    }

    public static function buildClauses(
        string $table,
        string $select,
        ?string $orderBy = null,
        ?string $orderMode = null,
        ?int $startAt = null,
        ?int $endAt = null,
    ): string {
        return (
            self::buildSelect($table, $select)
            . self::buildOrder($orderBy, $orderMode)
            . self::buildLimit($startAt, $endAt)
        );
    }

    public static function validateIdentifier(string $name): void
    {
        SelectBuilder::validateIdentifier($name);
    }
}
