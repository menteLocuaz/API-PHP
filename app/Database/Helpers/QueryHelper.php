<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database\Helpers;

use Arancamon\ApiPhp\Database\QueryBuilder;

class QueryHelper
{
    public static function parseRelations(string $rel, string $type): array
    {
        return [
            self::split($rel),
            self::split($type),
        ];
    }

    public static function split(?string $value): array
    {
        return $value === null || $value === ''
            ? []
            : array_map('trim', explode(',', $value));
    }

    public static function splitAndValidate(string $csv): array
    {
        $parts = self::split($csv);

        foreach ($parts as $part) {
            QueryBuilder::validateIdentifier($part);
        }

        return $parts;
    }

    public static function buildParams(array $keys, array $values): array
    {
        $params = [];

        foreach ($keys as $i => $key) {
            $params[':' . $key] = $values[$i] ?? null;
        }

        return $params;
    }

    public static function buildInFilter(?string $filterTo, ?string $inTo): string
    {
        if ($filterTo !== null && $inTo !== null) {
            return \Arancamon\ApiPhp\Database\Builders\RangeBuilder::buildInFilter($filterTo, $inTo);
        }

        return '';
    }
}
