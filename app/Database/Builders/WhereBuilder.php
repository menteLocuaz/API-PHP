<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database\Builders;

class WhereBuilder
{
    public static function build(array $conditions): string
    {
        if ($conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    public static function buildConditionsFromLinkTo(string $linkTo): array
    {
        $fields = explode(',', $linkTo);
        $conditions = [];

        foreach ($fields as $field) {
            SelectBuilder::validateIdentifier($field);
            $conditions[] = "{$field} = :{$field}";
        }

        return [
            'conditions' => $conditions,
            'fields' => $fields,
        ];
    }

    public static function buildFiltersWithQualification(string $linkTo, string $mainTable): array
    {
        $rawFields = explode(',', $linkTo);
        $fields = [];

        foreach ($rawFields as $f) {
            SelectBuilder::validateIdentifier($f);
            $fields[] = str_contains($f, '.') ? $f : "{$mainTable}.{$f}";
        }

        $paramNames = array_map(fn(string $f) => str_replace('.', '_', $f), $fields);
        $conditions = [];

        foreach ($fields as $key => $field) {
            $conditions[] = "{$field} = :{$paramNames[$key]}";
        }

        return [
            'conditions' => $conditions,
            'fields' => $fields,
            'paramNames' => $paramNames,
        ];
    }
}
