<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Models;

use Arancamon\ApiPhp\Database\Builders\RangeBuilder;
use Arancamon\ApiPhp\Database\Builders\SearchBuilder;
use Arancamon\ApiPhp\Database\Builders\WhereBuilder;
use Arancamon\ApiPhp\Database\Connection;
use Arancamon\ApiPhp\Database\Helpers\QueryHelper;
use Arancamon\ApiPhp\Database\QueryBuilder;

class GetModel
{
    public static function find(
        string $table,
        string $select,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): array {
        return Connection::execute(QueryBuilder::buildClauses($table, $select, $orderBy, $orderMode, $startAt, $endAt));
    }

    public static function findWithFilters(
        string $table,
        string $select,
        string $linkTo,
        string $equalTo,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): array {
        $info = WhereBuilder::buildConditionsFromLinkTo($linkTo);

        $params = QueryHelper::buildParams(QueryHelper::split($linkTo), explode('_', $equalTo));

        $sql =
            QueryBuilder::buildSelect($table, $select)
            . QueryBuilder::buildWhere($info['conditions'])
            . QueryBuilder::buildOrder($orderBy, $orderMode)
            . QueryBuilder::buildLimit($startAt, $endAt);

        return Connection::execute($sql, $params);
    }

    public static function findRelations(
        string $rel,
        string $type,
        string $select,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): ?array {
        [$relArray, $typeArray] = QueryHelper::parseRelations($rel, $type);

        if ($relArray[0] === '') {
            return null;
        }

        $joinClause = QueryBuilder::buildJoin($relArray, $typeArray);

        $sql =
            QueryBuilder::buildSelect($relArray[0], $select)
            . ($joinClause !== '' ? " {$joinClause}" : '')
            . QueryBuilder::buildOrder($orderBy, $orderMode)
            . QueryBuilder::buildLimit($startAt, $endAt);

        return Connection::execute($sql);
    }

    public static function findRelationsWithFilters(
        string $rel,
        string $type,
        string $select,
        ?string $linkTo,
        ?string $equalTo,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): ?array {
        [$relArray, $typeArray] = QueryHelper::parseRelations($rel, $type);

        if ($relArray[0] === '') {
            return null;
        }

        $joinClause = QueryBuilder::buildJoin($relArray, $typeArray);

        $sql = QueryBuilder::buildSelect($relArray[0], $select) . ($joinClause !== '' ? " {$joinClause}" : '');

        $params = [];

        if ($linkTo !== null && $equalTo !== null) {
            $info = WhereBuilder::buildFiltersWithQualification($linkTo, $relArray[0]);

            $sql .= QueryBuilder::buildWhere($info['conditions']);

            $values = explode('_', $equalTo);

            foreach ($info['fields'] as $key => $field) {
                $paramName = str_replace('.', '_', $field);
                $params[':' . $paramName] = $values[$key] ?? null;
            }
        }

        $sql .= QueryBuilder::buildOrder($orderBy, $orderMode) . QueryBuilder::buildLimit($startAt, $endAt);

        return Connection::execute($sql, $params);
    }

    public static function searchRelations(
        string $rel,
        string $type,
        string $select,
        string $linkTo,
        string $search,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): ?array {
        $linkToArray = QueryHelper::splitAndValidate($linkTo);
        $searchArray = QueryHelper::split($search);

        [$relArray, $typeArray] = QueryHelper::parseRelations($rel, $type);
        $joinClause = QueryBuilder::buildJoin($relArray, $typeArray);

        if (count($relArray) > 1) {
            $searchInfo = SearchBuilder::buildRelationsConditions($linkTo);

            $sql =
                QueryBuilder::buildSelect($relArray[0], $select)
                . ($joinClause !== '' ? " {$joinClause}" : '')
                . " WHERE CAST({$searchInfo['firstColumn']} AS TEXT) ILIKE :search0"
                . " {$searchInfo['extraConditionSql']}"
                . QueryBuilder::buildOrder($orderBy, $orderMode)
                . QueryBuilder::buildLimit($startAt, $endAt);

            $params = [':search0' => "%{$searchArray[0]}%"];

            foreach ($searchInfo['extraFields'] as $key => $field) {
                $paramName = str_replace('.', '_', $field);
                $params[':' . $paramName] = $searchArray[$key + 1] ?? null;
            }

            return Connection::execute($sql, $params);
        }

        return null;
    }

    public static function search(
        string $table,
        string $select,
        string $linkTo,
        string $search,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): array {
        $conditions = SearchBuilder::buildConditions($linkTo);

        $sql =
            QueryBuilder::buildSelect($table, $select)
            . ' WHERE '
            . implode(' OR ', $conditions)
            . QueryBuilder::buildOrder($orderBy, $orderMode)
            . QueryBuilder::buildLimit($startAt, $endAt);

        $searchValue = $search;

        if (!mb_check_encoding($search ?? '', 'UTF-8')) {
            $searchValue = iconv('ISO-8859-1', 'UTF-8//IGNORE', $search ?? '');
        }

        return Connection::execute($sql, [':search' => "%{$searchValue}%"]);
    }

    public static function findBetween(
        string $table,
        string $select,
        string $linkTo,
        string $between1,
        string $between2,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
        ?string $filterTo,
        ?string $inTo,
    ): ?array {
        $linkToArray = QueryHelper::splitAndValidate($linkTo);
        $filterToArray = $filterTo !== null ? QueryHelper::splitAndValidate($filterTo) : [];

        $selectArray = array_unique(array_merge(QueryHelper::split($select), $linkToArray, $filterToArray));

        if (empty(Connection::getColumnsData($table, $selectArray))) {
            return null;
        }

        $filter = QueryHelper::buildInFilter($filterTo, $inTo);

        $betweenColumn = $linkToArray[0];
        $condition = RangeBuilder::buildCondition($betweenColumn);

        $sql =
            QueryBuilder::buildSelect($table, $select)
            . " WHERE {$condition}"
            . ($filter !== '' ? " {$filter}" : '')
            . QueryBuilder::buildOrder($orderBy, $orderMode)
            . QueryBuilder::buildLimit($startAt, $endAt);

        return Connection::execute($sql, [
            ':between_from' => $between1,
            ':between_to' => $between2,
        ]);
    }

    public static function findRelationsBetween(
        string $rel,
        string $type,
        string $select,
        string $linkTo,
        string $between1,
        string $between2,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
        ?string $filterTo,
        ?string $inTo,
    ): ?array {
        $linkToArray = QueryHelper::splitAndValidate($linkTo);
        $relArray = QueryHelper::splitAndValidate($rel);
        $typeArray = QueryHelper::splitAndValidate($type);

        $filter = QueryHelper::buildInFilter($filterTo, $inTo);

        if (count($relArray) > 1) {
            foreach ($relArray as $value) {
                if (empty(Connection::getColumnsData($value, ['*']))) {
                    return null;
                }
            }

            $innerJoinText = QueryBuilder::buildJoin($relArray, $typeArray);

            $betweenColumn = $linkToArray[0];
            $condition = RangeBuilder::buildCondition($betweenColumn);

            $sql =
                QueryBuilder::buildSelect($relArray[0], $select)
                . " {$innerJoinText}"
                . " WHERE {$condition}"
                . ($filter !== '' ? " {$filter}" : '')
                . QueryBuilder::buildOrder($orderBy, $orderMode)
                . QueryBuilder::buildLimit($startAt, $endAt);

            return Connection::execute($sql, [
                ':between_from' => $between1,
                ':between_to' => $between2,
            ]);
        }

        return null;
    }
}
