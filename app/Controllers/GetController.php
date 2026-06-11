<?php

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Models\GetModel;

class GetController
{
    public static function GetData(
        string $table,
        string $select,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::GetData($table, $select, $orderBy, $orderMode, $startAt, $endAt);

        self::response($response);
    }

    public static function GetDataFilter(
        string $table,
        string $select,
        string $linkTo,
        mixed $equalTo,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::GetDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt);

        self::response($response);
    }

    public static function GetRelData(
        string $rel,
        string $type,
        string $select,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::GetRelData($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt);

        self::response($response);
    }

    public static function GetRelDataFilter(
        string $rel,
        string $type,
        string $select,
        string $linkTo,
        mixed $equalTo,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::GetRelDataFilter(
            $rel,
            $type,
            $select,
            $linkTo,
            $equalTo,
            $orderBy,
            $orderMode,
            $startAt,
            $endAt,
        );

        self::response($response);
    }
    // Peticiones GET paar el buscador sin relacion 
    public static function GetDataSearch(
        string $table,
        string $select,
        string $linkTo,
        mixed $search,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::GetDataSearch($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt);

        self::response($response);
    }

    // Repuesta del controlador
    private static function response(array $response): void
    {
        if (!empty($response)) {
            $status = 200;

            $json = [
                'status' => $status,
                'total' => count($response),
                'results' => $response,
            ];
        } else {
            $status = 404;

            $json = [
                'status' => $status,
                'results' => 'Not found',
            ];
        }

        http_response_code($status);

        header('Content-Type: application/json');

        echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
