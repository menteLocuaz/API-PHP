<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database\Builders;

class SearchBuilder
{
    public static function buildConditions(string $linkTo): array
    {
        $fields = explode(',', $linkTo);
        $conditions = [];

        foreach ($fields as $field) {
            SelectBuilder::validateIdentifier($field);
            $conditions[] = "CAST({$field} AS TEXT) ILIKE :search";
        }

        return $conditions;
    }

    public static function buildRelationsConditions(string $linkTo): array
    {
        $linkToArray = explode(',', $linkTo);

        foreach ($linkToArray as $lt) {
            SelectBuilder::validateIdentifier($lt);
        }

        $linkToText = '';
        $extraFields = [];

        if (count($linkToArray) > 1) {
            for ($key = 1; $key < count($linkToArray); $key++) {
                $paramName = str_replace('.', '_', $linkToArray[$key]);
                $linkToText .= "AND {$linkToArray[$key]} = :{$paramName} ";
                $extraFields[] = $linkToArray[$key];
            }
        }

        return [
            'firstColumn' => $linkToArray[0],
            'extraConditionSql' => $linkToText,
            'extraFields' => $extraFields,
        ];
    }
}
