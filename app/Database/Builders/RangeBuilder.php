<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database\Builders;

class RangeBuilder
{
    public static function buildCondition(string $column): string
    {
        SelectBuilder::validateIdentifier($column);

        return "{$column} BETWEEN :between_from AND :between_to";
    }

    public static function buildInFilter(string $filterTo, string $inTo): string
    {
        SelectBuilder::validateIdentifier($filterTo);

        return "AND {$filterTo} IN ({$inTo})";
    }
}
