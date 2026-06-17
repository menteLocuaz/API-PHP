<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Http\Response;
use Arancamon\ApiPhp\Models\GetModel;

class GetController
{
    public function find(
        string $table,
        string $select,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::find($table, $select, $orderBy, $orderMode, $startAt, $endAt);
        $this->response($response);
    }

    public function findWithFilters(
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
        $this->response($response);
    }

    public function findRelations(
        string $rel,
        string $type,
        string $select,
        ?string $orderBy,
        ?string $orderMode,
        ?int $startAt,
        ?int $endAt,
    ): void {
        $response = GetModel::findRelations($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt);
        $this->response($response);
    }

    public function findRelationsWithFilters(
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
        $this->response($response);
    }

    public function searchRelations(
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
        $this->response($response);
    }

    public function search(
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
        $this->response($response);
    }

    public function findBetween(
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
        $this->response($response);
    }

    public function findRelationsBetween(
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
        $this->response($response);
    }

    private function response(?array $response): void
    {
        if (!empty($response)) {
            Response::json($response);
        } else {
            Response::notFound();
        }
    }
}
