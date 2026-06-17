<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Services;

use Arancamon\ApiPhp\Controllers\GetController;

class GetService
{
    public function handle(string $table, array $getParams): void
    {
        $params = [
            'select' => $getParams['select'] ?? '*',
            'orderBy' => $getParams['orderBy'] ?? null,
            'orderMode' => $getParams['orderMode'] ?? null,
            'startAt' => $getParams['startAt'] ?? null,
            'endAt' => $getParams['endAt'] ?? null,
            'linkTo' => $getParams['linkTo'] ?? null,
            'equalTo' => $getParams['equalTo'] ?? null,
            'rel' => $getParams['rel'] ?? null,
            'type' => $getParams['type'] ?? null,
            'search' => $getParams['search'] ?? null,
            'between1' => $getParams['between1'] ?? null,
            'between2' => $getParams['between2'] ?? null,
            'filterTo' => $getParams['filterTo'] ?? null,
            'inTo' => $getParams['inTo'] ?? null,
        ];

        $isRelation = $table === 'relations';

        $hasFilter = !empty($params['linkTo']) && !empty($params['equalTo']);
        $hasSearch = !empty($params['linkTo']) && !empty($params['search']);
        $hasRange = !empty($params['linkTo']) && !empty($params['between1']) && !empty($params['between2']);

        match (true) {
            // Relaciones con rango
            $isRelation && $hasRange => GetController::findRelationsBetween(
                $params['rel'],
                $params['type'],
                $params['select'],
                $params['linkTo'],
                $params['between1'],
                $params['between2'],
                $params['orderBy'],
                $params['orderMode'],
                $params['startAt'],
                $params['endAt'],
                $params['filterTo'],
                $params['inTo'],
            ),
            // Relaciones con filtro
            $isRelation && $hasFilter => GetController::findRelationsWithFilters(
                $params['rel'],
                $params['type'],
                $params['select'],
                $params['linkTo'],
                $params['equalTo'],
                $params['orderBy'],
                $params['orderMode'],
                $params['startAt'],
                $params['endAt'],
            ),
            // Relaciones con busqueda
            $isRelation && $hasSearch => GetController::searchRelations(
                $params['rel'],
                $params['type'],
                $params['select'],
                $params['linkTo'],
                $params['search'],
                $params['orderBy'],
                $params['orderMode'],
                $params['startAt'],
                $params['endAt'],
            ),
            // Relaciones sin filtro
            $isRelation => GetController::findRelations(
                $params['rel'],
                $params['type'],
                $params['select'],
                $params['orderBy'],
                $params['orderMode'],
                $params['startAt'],
                $params['endAt'],
            ),
            // Búsqueda
            $hasSearch => GetController::search(
                $table,
                $params['select'],
                $params['linkTo'],
                $params['search'],
                $params['orderBy'],
                $params['orderMode'],
                $params['startAt'],
                $params['endAt'],
            ),
            // Rango
            $hasRange => GetController::findBetween(
                $table,
                $params['select'],
                $params['linkTo'],
                $params['between1'],
                $params['between2'],
                $params['orderBy'],
                $params['orderMode'],
                $params['startAt'],
                $params['endAt'],
                $params['filterTo'],
                $params['inTo'],
            ),
            // Filtro normal
            $hasFilter => GetController::findWithFilters(
                $table,
                $params['select'],
                $params['linkTo'],
                $params['equalTo'],
                $params['orderBy'],
                $params['orderMode'],
                $params['startAt'],
                $params['endAt'],
            ),
            // Sin filtros
            default => GetController::find(
                $table,
                $params['select'],
                $params['orderBy'],
                $params['orderMode'],
                $params['startAt'],
                $params['endAt'],
            ),
        };
    }
}
