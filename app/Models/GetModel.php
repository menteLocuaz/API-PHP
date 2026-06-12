<?php

namespace Arancamon\ApiPhp\Models;

use Arancamon\ApiPhp\Models\Connection;
use InvalidArgumentException;
use PDO;
use PDOException;

class GetModel
{
    // Peticion sin filtro
    public static function find($table, $select, $orderBy, $orderMode, $startAt, $endAt)
    {
        $SQL =
            self::buildSelect($table, $select)
            . self::buildOrder($orderBy, $orderMode)
            . self::buildLimit($startAt, $endAt);

        return self::execute($SQL);
    }

    // Peticion con filtro
    public static function findWithFilters($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {
        $SQL = self::buildFilterQuery($table, $select, $linkTo, $orderBy, $orderMode, $startAt, $endAt);
        $fields = explode(',', $linkTo);
        $values = explode('_', $equalTo);
        $params = [];
        foreach ($fields as $key => $field) {
            $params[':' . $field] = $values[$key] ?? null;
        }

        return self::execute($SQL, $params);
    }

    private static function buildFilterQuery($table, $select, $linkTo, $orderBy, $orderMode, $startAt, $endAt)
    {
        $fields = explode(',', $linkTo);
        $conditions = [];

        foreach ($fields as $field) {
            self::validateIdentifier($field);
            $conditions[] = "$field = :$field";
        }

        return (
            self::buildSelect($table, $select)
            . self::buildWhere($conditions)
            . self::buildOrder($orderBy, $orderMode)
            . self::buildLimit($startAt, $endAt)
        );
    }

    private static function execute(string $sql, array $params = []): array
    {
        $stmt = Connection::Connect()->prepare($sql);

        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    private static function validateIdentifier(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9_.,*]+$/', $name)) {
            throw new InvalidArgumentException("Invalid identifier: $name");
        }
    }

    private static function buildSelect(string $table, string $select): string
    {
        self::validateIdentifier($table);
        self::validateIdentifier($select);

        return "SELECT $select FROM $table";
    }

    private static function buildWhere(array $conditions): string
    {
        if ($conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    private static function buildOrder(?string $orderBy, ?string $orderMode): string
    {
        if ($orderBy === null || $orderMode === null) {
            return '';
        }

        self::validateIdentifier($orderBy);

        $mode = strtoupper($orderMode);
        if (!in_array($mode, ['ASC', 'DESC'], true)) {
            throw new InvalidArgumentException("Invalid ORDER BY mode: $orderMode");
        }

        return " ORDER BY $orderBy $mode";
    }

    private static function buildLimit(?int $startAt, ?int $endAt): string
    {
        if ($startAt === null || $endAt === null) {
            return '';
        }

        return " LIMIT $endAt OFFSET $startAt";
    }

    // Peticion relacion sin filtro
    public static function findRelations($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt)
    {
        $relArray = explode(',', $rel);
        $typeArray = explode(',', $type);

        if (count($relArray) < 1) {
            return null;
        }

        $innerJoinTxt = '';
        if (count($relArray) > 1) {
            foreach ($relArray as $relTable) {
                self::validateIdentifier($relTable);
            }
            foreach ($typeArray as $typeVal) {
                self::validateIdentifier($typeVal);
            }
            foreach ($relArray as $key => $values) {
                if ($key > 0) {
                    if ($key === 1) {
                        $innerJoinTxt .= "INNER JOIN $values ON {$relArray[0]}.id_{$typeArray[0]} = $values.id_{$typeArray[0]}_{$typeArray[1]} ";
                    } else {
                        $innerJoinTxt .= "INNER JOIN $values ON {$relArray[1]}.id_{$typeArray[$key]}_{$typeArray[1]} = $values.id_{$typeArray[$key]} ";
                    }
                }
            }
        }

        $SQL =
            self::buildSelect($relArray[0], $select)
            . ($innerJoinTxt !== '' ? " $innerJoinTxt" : '')
            . self::buildOrder($orderBy, $orderMode)
            . self::buildLimit($startAt, $endAt);

        return self::execute($SQL);
    }

    // Peticiones relacional con filtro
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

        $innerJoinTxt = '';
        if (count($relArray) > 1) {
            foreach ($relArray as $relTable) {
                self::validateIdentifier($relTable);
            }
            foreach ($typeArray as $typeVal) {
                self::validateIdentifier($typeVal);
            }
            foreach ($relArray as $key => $values) {
                if ($key > 0) {
                    if ($key === 1) {
                        $innerJoinTxt .= "INNER JOIN $values ON {$relArray[0]}.id_{$typeArray[0]} = $values.id_{$typeArray[0]}_{$typeArray[1]} ";
                    } else {
                        $innerJoinTxt .= "INNER JOIN $values ON {$relArray[1]}.id_{$typeArray[$key]}_{$typeArray[1]} = $values.id_{$typeArray[$key]} ";
                    }
                }
            }
        }

        $params = [];

        $SQL = self::buildSelect($relArray[0], $select) . ($innerJoinTxt !== '' ? " $innerJoinTxt" : '');

        if ($linkTo != null && $equalTo != null) {
            $rawFields = explode(',', $linkTo);
            $fields = [];
            foreach ($rawFields as $f) {
                self::validateIdentifier($f);
                $fields[] = str_contains($f, '.') ? $f : "{$relArray[0]}.$f";
            }
            $paramNames = array_map(fn($f) => str_replace('.', '_', $f), $fields);
            $conditions = [];
            foreach ($fields as $key => $field) {
                $conditions[] = "{$fields[$key]} = :{$paramNames[$key]}";
            }

            $SQL .= self::buildWhere($conditions);

            $values = explode('_', $equalTo);
            foreach ($fields as $key => $field) {
                $paramName = str_replace('.', '_', $field);
                $params[':' . $paramName] = $values[$key] ?? null;
            }
        }

        $SQL .= self::buildOrder($orderBy, $orderMode) . self::buildLimit($startAt, $endAt);

        return self::execute($SQL, $params);
    }

    // Busqueda con el like entre tablas relacionadas
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
            self::validateIdentifier($lt);
        }
        $searchArray = explode(',', $search);
        $linkToText = '';

        if (count($linkToArray) > 1) {
            foreach ($linkToArray as $key => $value) {
                if ($key > 0) {
                    $linkToText .= 'AND ' . $value . ' = :' . str_replace('.', '_', $value) . ' ';
                }
            }
        }

        $relArray = explode(',', $rel);
        $typeArray = explode(',', $type);
        foreach ($relArray as $relTable) {
            self::validateIdentifier($relTable);
        }
        foreach ($typeArray as $typeVal) {
            self::validateIdentifier($typeVal);
        }
        $innerJoinText = '';

        if (count($relArray) > 1) {
            foreach ($relArray as $key => $values) {
                if ($key > 0) {
                    if ($key === 1) {
                        $innerJoinText .= "INNER JOIN $values ON {$relArray[0]}.id_{$typeArray[0]} = $values.id_{$typeArray[0]}_{$typeArray[1]} ";
                    } else {
                        $innerJoinText .= "INNER JOIN $values ON {$relArray[1]}.id_{$typeArray[$key]}_{$typeArray[1]} = $values.id_{$typeArray[$key]} ";
                    }
                }
            }

            $sql =
                self::buildSelect($relArray[0], $select)
                . " $innerJoinText"
                . " WHERE CAST($linkToArray[0] AS TEXT) ILIKE :search0 $linkToText"
                . self::buildOrder($orderBy, $orderMode)
                . self::buildLimit($startAt, $endAt);

            $params = [':search0' => "%{$searchArray[0]}%"];
            foreach ($linkToArray as $key => $value) {
                if ($key > 0) {
                    $paramName = str_replace('.', '_', $value);
                    $params[':' . $paramName] = $searchArray[$key] ?? null;
                }
            }

            try {
                return self::execute($sql, $params);
            } catch (PDOException $Exception) {
                return null;
            }
        } else {
            return null;
        }
    }

    // Busqueda con el like
    public static function search($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
    {
        $fields = explode(',', $linkTo);

        $conditions = [];

        foreach ($fields as $field) {
            self::validateIdentifier($field);
            $conditions[] = "CAST($field AS TEXT) ILIKE :search";
        }

        $SQL =
            self::buildSelect($table, $select)
            . ' WHERE '
            . implode(' OR ', $conditions)
            . self::buildOrder($orderBy, $orderMode)
            . self::buildLimit($startAt, $endAt);

        try {
            $searchValue = $search;
            if (!mb_check_encoding($search ?? '', 'UTF-8')) {
                $searchValue = iconv('ISO-8859-1', 'UTF-8//IGNORE', $search ?? '');
            }
            return self::execute($SQL, [':search' => "%{$searchValue}%"]);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Peticiones GET para seleccion de rangos
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
            self::validateIdentifier($lt);
        }

        if ($filterTo != null) {
            $filterToArray = explode(',', $filterTo);
            foreach ($filterToArray as $ft) {
                self::validateIdentifier($ft);
            }
        } else {
            $filterToArray = [];
        }

        $selectArray = explode(',', $select);

        foreach ($linkToArray as $key => $value) {
            array_push($selectArray, $value);
        }

        foreach ($filterToArray as $key => $value) {
            array_push($selectArray, $value);
        }

        $selectArray = array_unique($selectArray);

        if (empty(Connection::getColumnsData($table, $selectArray))) {
            return null;
        }

        $filter = '';

        if ($filterTo != null && $inTo != null) {
            $filter = 'AND ' . $filterTo . ' IN (' . $inTo . ')';
        }

        $sql =
            self::buildSelect($table, $select)
            . " WHERE $linkTo BETWEEN '$between1' AND '$between2'"
            . ($filter !== '' ? " $filter" : '')
            . self::buildOrder($orderBy, $orderMode)
            . self::buildLimit($startAt, $endAt);

        try {
            return self::execute($sql);
        } catch (PDOException $Exception) {
            return null;
        }
    }

    // Peticiones GET para seleccion de rangos con relaciones
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
            self::validateIdentifier($lt);
        }

        if ($filterTo != null) {
            $filterToArray = explode(',', $filterTo);
            foreach ($filterToArray as $ft) {
                self::validateIdentifier($ft);
            }
        } else {
            $filterToArray = [];
        }

        $filter = '';

        if ($filterTo != null && $inTo != null) {
            $filter = 'AND ' . $filterTo . ' IN (' . $inTo . ')';
        }

        $relArray = explode(',', $rel);
        $typeArray = explode(',', $type);
        foreach ($relArray as $relTable) {
            self::validateIdentifier($relTable);
        }
        foreach ($typeArray as $typeVal) {
            self::validateIdentifier($typeVal);
        }
        $innerJoinText = '';

        if (count($relArray) > 1) {
            foreach ($relArray as $key => $value) {
                if (empty(Connection::getColumnsData($value, ['*']))) {
                    return null;
                }

                if ($key > 0) {
                    $innerJoinText .=
                        'INNER JOIN '
                        . $value
                        . ' ON '
                        . $relArray[0]
                        . '.id_'
                        . $typeArray[$key]
                        . '_'
                        . $typeArray[0]
                        . ' = '
                        . $value
                        . '.id_'
                        . $typeArray[$key]
                        . ' ';
                }
            }

            $sql =
                self::buildSelect($relArray[0], $select)
                . " $innerJoinText"
                . " WHERE $linkTo BETWEEN '$between1' AND '$between2'"
                . ($filter !== '' ? " $filter" : '')
                . self::buildOrder($orderBy, $orderMode)
                . self::buildLimit($startAt, $endAt);

            try {
                return self::execute($sql);
            } catch (PDOException $Exception) {
                return null;
            }
        } else {
            return null;
        }
    }
}
