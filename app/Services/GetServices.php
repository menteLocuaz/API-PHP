<?php

use Arancamon\ApiPhp\Controllers\GetController;

$table = explode('?', $routesArray[1])[0];

$params = [
    'select' => $_GET['select'] ?? '*',
    'orderBy' => $_GET['orderBy'] ?? null,
    'orderMode' => $_GET['orderMode'] ?? null,
    'startAt' => $_GET['startAt'] ?? null,
    'endAt' => $_GET['endAt'] ?? null,
    'linkTo' => $_GET['linkTo'] ?? null,
    'equalTo' => $_GET['equalTo'] ?? null,
    'rel' => $_GET['rel'] ?? null,
    'type' => $_GET['type'] ?? null,
    'search' => $_GET['search'] ?? null,
    'between1' => $_GET['between1'] ?? null,
    'between2' => $_GET['between2'] ?? null,
    'filterTo' => $_GET['filterTo'] ?? null,
    'inTo' => $_GET['inTo'] ?? null,
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

exit();
