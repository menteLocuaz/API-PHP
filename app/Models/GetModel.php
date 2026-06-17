<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Models;

use Arancamon\ApiPhp\Database\Builders\RangeBuilder;
use Arancamon\ApiPhp\Database\Builders\SearchBuilder;
use Arancamon\ApiPhp\Database\Builders\WhereBuilder;
use Arancamon\ApiPhp\Database\Connection;
use Arancamon\ApiPhp\Database\QueryBuilder;
use PDOException;

class GetModel
{
    public static function find($table, $select, $orderBy, $orderMode, $startAt, $endAt)
    {
        $sql = QueryBuilder::buildClauses($table, $select, $orderBy, $orderMode, $startAt, $endAt);

        return Connection::execute($sql);
    }

    public static function findWithFilters($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {
        $info = WhereBuilder::buildConditionsFromLinkTo($linkTo);

        $fields = explode(',', $linkTo);
        $values = explode('_', $equalTo);
        $params = [];

        foreach ($fields as $key => $field) {
            $params[':' . $field] = $values[$key] ?? null;
        }

        $sql =
            QueryBuilder::buildSelect($table, $select)
            . QueryBuilder::buildWhere($info['conditions'])
            . QueryBuilder::buildOrder($orderBy, $orderMode)
            . QueryBuilder::buildLimit($startAt, $endAt);

        return Connection::execute($sql, $params);
    }

    public static function findRelations($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt)
    {
        $relArray = explode(',', $rel);
        $typeArray = explode(',', $type);

        if (count($relArray) < 1) {
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
        $rel,
        $type,
        $select,
        $linkTo,
        $equalTo,
        $orderBy,
        $orderMode,
        $startAt,
        $endAt,
    ) {
        $relArray = explode(',', $rel);
        $typeArray = explode(',', $type);

        if (count($relArray) < 1) {
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
        $rel,
        $type,
        $select,
        $linkTo,
        $search,
        $orderBy,
        $orderMode,
        $startAt,
        $endAt,
    ) {
        $linkToArray = explode(',', $linkTo);

        foreach ($linkToArray as $lt) {
            QueryBuilder::validateIdentifier($lt);
        }

        $searchArray = explode(',', $search);

        $relArray = explode(',', $rel);
        $typeArray = explode(',', $type);
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

            try {
                return Connection::execute($sql, $params);
            } catch (PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }

        return null;
    }

    public static function search($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
    {
        $conditions = SearchBuilder::buildConditions($linkTo);

        $sql =
            QueryBuilder::buildSelect($table, $select)
            . ' WHERE '
            . implode(' OR ', $conditions)
            . QueryBuilder::buildOrder($orderBy, $orderMode)
            . QueryBuilder::buildLimit($startAt, $endAt);

        try {
            $searchValue = $search;

            if (!mb_check_encoding($search ?? '', 'UTF-8')) {
                $searchValue = iconv('ISO-8859-1', 'UTF-8//IGNORE', $search ?? '');
            }

            return Connection::execute($sql, [':search' => "%{$searchValue}%"]);
        } catch (PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function findBetween(
        $table,
        $select,
        $linkTo,
        $between1,
        $between2,
        $orderBy,
        $orderMode,
        $startAt,
        $endAt,
        $filterTo,
        $inTo,
    ) {
        $linkToArray = explode(',', $linkTo);

        foreach ($linkToArray as $lt) {
            QueryBuilder::validateIdentifier($lt);
        }

        if ($filterTo !== null) {
            $filterToArray = explode(',', $filterTo);

            foreach ($filterToArray as $ft) {
                QueryBuilder::validateIdentifier($ft);
            }
        } else {
            $filterToArray = [];
        }

        $selectArray = array_unique(array_merge(
            explode(',', $select),
            $linkToArray,
            $filterTo !== null ? explode(',', $filterTo) : [],
        ));

        if (empty(Connection::getColumnsData($table, $selectArray))) {
            return null;
        }

        $filter = '';

        if ($filterTo !== null && $inTo !== null) {
            $filter = RangeBuilder::buildInFilter($filterTo, $inTo);
        }

        $betweenColumn = $linkToArray[0];
        $condition = RangeBuilder::buildCondition($betweenColumn);

        $sql =
            QueryBuilder::buildSelect($table, $select)
            . " WHERE {$condition}"
            . ($filter !== '' ? " {$filter}" : '')
            . QueryBuilder::buildOrder($orderBy, $orderMode)
            . QueryBuilder::buildLimit($startAt, $endAt);

        try {
            return Connection::execute($sql, [
                ':between_from' => $between1,
                ':between_to' => $between2,
            ]);
        } catch (PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function findRelationsBetween(
        $rel,
        $type,
        $select,
        $linkTo,
        $between1,
        $between2,
        $orderBy,
        $orderMode,
        $startAt,
        $endAt,
        $filterTo,
        $inTo,
    ) {
        $linkToArray = explode(',', $linkTo);

        foreach ($linkToArray as $lt) {
            QueryBuilder::validateIdentifier($lt);
        }

        if ($filterTo !== null) {
            $filterToArray = explode(',', $filterTo);

            foreach ($filterToArray as $ft) {
                QueryBuilder::validateIdentifier($ft);
            }
        } else {
            $filterToArray = [];
        }

        $filter = '';

        if ($filterTo !== null && $inTo !== null) {
            $filter = RangeBuilder::buildInFilter($filterTo, $inTo);
        }

        $relArray = explode(',', $rel);
        $typeArray = explode(',', $type);

        foreach ($relArray as $relTable) {
            QueryBuilder::validateIdentifier($relTable);
        }
        foreach ($typeArray as $typeVal) {
            QueryBuilder::validateIdentifier($typeVal);
        }

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

            try {
                return Connection::execute($sql, [
                    ':between_from' => $between1,
                    ':between_to' => $between2,
                ]);
            } catch (PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }

        return null;
    }
}
