<?php

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Models\GetModel;

class GetController
{
    public static function find(
        string $table,
        string $select,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::find($table, $select, $orderBy, $orderMode, $startAt, $endAt);

        self::response($response);
    }

    public static function findWithFilters(
        string $table,
        string $select,
        string $linkTo,
        mixed $equalTo,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::findWithFilters(
            $table,
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

    public static function findRelations(
        string $rel,
        string $type,
        string $select,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::findRelations($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt);

        self::response($response);
    }

    public static function findRelationsWithFilters(
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
        $response = GetModel::findRelationsWithFilters(
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

    // Peticiones GET para el buscador entre tablas relacionadas
    public static function searchRelations(
        string $rel,
        string $type,
        string $select,
        string $linkTo,
        mixed $search,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::searchRelations(
            $rel,
            $type,
            $select,
            $linkTo,
            $search,
            $orderBy,
            $orderMode,
            $startAt,
            $endAt,
        );

        self::response($response);
    }

    // Peticiones GET paar el buscador sin relacion
    public static function search(
        string $table,
        string $select,
        string $linkTo,
        mixed $search,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::search($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt);

        self::response($response);
    }

    // Peticiones GET para seleccion de rangos
    public static function findBetween(
        string $table,
        string $select,
        string $linkTo,
        mixed $between1,
        mixed $between2,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
        ?string $filterTo,
        ?string $inTo,
    ): void {
        $response = GetModel::findBetween(
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
        );

        self::response($response);
    }

    // Peticiones GET para seleccion de rangos con relaciones
    public static function findRelationsBetween(
        string $rel,
        string $type,
        string $select,
        string $linkTo,
        mixed $between1,
        mixed $between2,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
        ?string $filterTo,
        ?string $inTo,
    ): void {
        $response = GetModel::findRelationsBetween(
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
        );

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
