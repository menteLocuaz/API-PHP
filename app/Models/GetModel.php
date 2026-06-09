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
}
