<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database\Builders;

use Arancamon\ApiPhp\Database\Exceptions\QueryBuilderException;

class SelectBuilder
{
    public static function build(string $table, string $select): string
    {
        self::validateIdentifier($table);
        self::validateIdentifier($select);

        return "SELECT {$select} FROM {$table}";
    }

    public static function buildOrder(?string $orderBy, ?string $orderMode): string
    {
        if ($orderBy === null || $orderMode === null) {
            return '';
        }

        self::validateIdentifier($orderBy);

        $mode = strtoupper($orderMode);
        if (!in_array($mode, ['ASC', 'DESC'], true)) {
            throw QueryBuilderException::invalidOrderMode($orderMode);
        }

        return " ORDER BY {$orderBy} {$mode}";
    }

    public static function buildLimit(?int $startAt, ?int $endAt): string
    {
        if ($startAt === null || $endAt === null) {
            return '';
        }

        return " LIMIT {$endAt} OFFSET {$startAt}";
    }

    public static function buildUpdate(string $table, array $columns, string $nameId): string
    {
        self::validateIdentifier($table);
        self::validateIdentifier($nameId);

        foreach ($columns as $column) {
            self::validateIdentifier($column);
        }

        $set = implode(
            ', ',
            array_map(
                fn ($col) => "{$col} = :{$col}",
                $columns
            )
        );

        return "UPDATE {$table} SET {$set} WHERE {$nameId} = :{$nameId}";
    }

    public static function validateIdentifier(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9_.,*]+$/', $name)) {
            throw QueryBuilderException::invalidIdentifier($name);
        }
    }
}
