<?php

namespace Arancamon\ApiPhp\Models;

use PDO;

class GetModel
{
    // Peticion sin filtro
    public static function GetData($table, $select, $orderBy, $orderMode, $startAt, $endAt)
    {
        $SQL = "SELECT $select FROM $table ";
        if ($orderBy != null && $orderMode != null && $startAt == null && $endAt == null) {
            $SQL = "SELECT $select FROM $table ORDER BY $orderBy $orderMode";
        }
        if ($orderBy != null && $orderMode != null && $startAt != null && $endAt != null) {
            $SQL = "SELECT $select FROM $table ORDER BY $orderBy $orderMode LIMIT $endAt OFFSET $startAt";
        }
        if ($orderBy == null && $orderMode == null && $startAt != null && $endAt != null) {
            $SQL = "SELECT $select FROM $table LIMIT $endAt OFFSET $startAt";
        }

        $stmt = Connection::Connect()->prepare($SQL);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    // Peticion con filtro
    public static function GetDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {
        $SQL = self::buildFilterQuery($table, $select, $linkTo, $orderBy, $orderMode, $startAt, $endAt);
        $stmt = Connection::Connect()->prepare($SQL);
        self::bindFilterParams($stmt, $linkTo, $equalTo);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    private static function buildFilterQuery($table, $select, $linkTo, $orderBy, $orderMode, $startAt, $endAt)
    {
        $fields = explode(',', $linkTo);
        $where = $fields[0] . ' = :' . $fields[0];

        for ($i = 1; $i < count($fields); $i++) {
            $where .= ' AND ' . $fields[$i] . ' = :' . $fields[$i];
        }

        $SQL = "SELECT $select FROM $table WHERE $where";
        if ($orderBy != null && $orderMode != null && $startAt == null && $endAt == null) {
            $SQL = "SELECT $select FROM $table WHERE $where ORDER BY $orderBy $orderMode";
        }
        if ($orderBy != null && $orderMode != null && $startAt != null && $endAt != null) {
            $SQL = "SELECT $select FROM $table WHERE $where ORDER BY $orderBy $orderMode LIMIT $endAt OFFSET $startAt";
        }
        if ($orderBy == null && $orderMode == null && $startAt != null && $endAt != null) {
            $SQL = "SELECT $select FROM $table WHERE $where LIMIT $endAt OFFSET $startAt";
        }
        return $SQL;
    }

    private static function bindFilterParams($stmt, $linkTo, $equalTo)
    {
        $fields = explode(',', $linkTo);
        $values = explode('_', $equalTo);

        foreach ($fields as $key => $field) {
            $stmt->bindValue(':' . $field, $values[$key] ?? null, PDO::PARAM_STR);
        }
    }

    // Peticion relacion sin filtro
    public static function GetRelData($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt)
    {
        $relArray = explode(',', $rel);
        $typeArray = explode(',', $type);

        if (count($relArray) < 1) {
            return null;
        }

        $innerJoinTxt = '';
        if (count($relArray) > 1) {
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

        $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt";
        if ($orderBy != null && $orderMode != null && $startAt === null && $endAt === null) {
            $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt ORDER BY $orderBy $orderMode";
        }
        if ($orderBy != null && $orderMode != null && $startAt !== null && $endAt !== null) {
            $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt ORDER BY $orderBy $orderMode LIMIT $endAt OFFSET $startAt";
        }
        if ($orderBy === null && $orderMode === null && $startAt !== null && $endAt !== null) {
            $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt LIMIT $endAt OFFSET $startAt";
        }

        $stmt = Connection::Connect()->prepare($SQL);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    // Peticiones relacional con filtro
    public static function GetRelDataFilter(
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

        $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt";
        if ($linkTo != null && $equalTo != null) {
            $rawFields = explode(',', $linkTo);
            $fields = [];
            foreach ($rawFields as $f) {
                $fields[] = str_contains($f, '.') ? $f : "{$relArray[0]}.$f";
            }
            $paramNames = array_map(fn($f) => str_replace('.', '_', $f), $fields);
            $where = $fields[0] . ' = :' . $paramNames[0];
            for ($i = 1; $i < count($fields); $i++) {
                $where .= ' AND ' . $fields[$i] . ' = :' . $paramNames[$i];
            }

            $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt WHERE $where";
            if ($orderBy != null && $orderMode != null && $startAt === null && $endAt === null) {
                $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt WHERE $where ORDER BY $orderBy $orderMode";
            }
            if ($orderBy != null && $orderMode != null && $startAt !== null && $endAt !== null) {
                $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt WHERE $where ORDER BY $orderBy $orderMode LIMIT $endAt OFFSET $startAt";
            }
            if ($orderBy === null && $orderMode === null && $startAt !== null && $endAt !== null) {
                $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt WHERE $where LIMIT $endAt OFFSET $startAt";
            }
        } else {
            if ($orderBy != null && $orderMode != null && $startAt === null && $endAt === null) {
                $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt ORDER BY $orderBy $orderMode";
            }
            if ($orderBy != null && $orderMode != null && $startAt !== null && $endAt !== null) {
                $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt ORDER BY $orderBy $orderMode LIMIT $endAt OFFSET $startAt";
            }
            if ($orderBy === null && $orderMode === null && $startAt !== null && $endAt !== null) {
                $SQL = "SELECT $select FROM {$relArray[0]} $innerJoinTxt LIMIT $endAt OFFSET $startAt";
            }
        }

        $stmt = Connection::Connect()->prepare($SQL);
        if ($linkTo != null && $equalTo != null) {
            $values = explode('_', $equalTo);
            foreach ($fields as $key => $field) {
                $paramName = str_replace('.', '_', $field);
                $stmt->bindValue(':' . $paramName, $values[$key] ?? null, PDO::PARAM_STR);
            }
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }
}
