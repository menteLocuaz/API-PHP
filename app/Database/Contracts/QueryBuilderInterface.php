<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database\Contracts;

interface QueryBuilderInterface
{
    public static function buildSelect(string $table, string $select): string;

    public static function buildWhere(array $conditions): string;

    public static function buildOrder(?string $orderBy, ?string $orderMode): string;

    public static function buildLimit(?int $startAt, ?int $endAt): string;

    public static function buildJoin(array $tables, array $types): string;

    public static function buildUpdate(string $table, array $columns, string $nameId): string;

    public static function validateIdentifier(string $name): void;
}
