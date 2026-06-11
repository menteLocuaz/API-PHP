<?php

use Arancamon\ApiPhp\Controllers\GetController;

$table = explode('?', $routesArray[0])[0];

$params = [
    'select'    => $_GET['select'] ?? '*',
    'orderBy'   => $_GET['orderBy'] ?? null,
    'orderMode' => $_GET['orderMode'] ?? null,
    'startAt'   => $_GET['startAt'] ?? null,
    'endAt'     => $_GET['endAt'] ?? null,
    'linkTo'    => $_GET['linkTo'] ?? null,
    'equalTo'   => $_GET['equalTo'] ?? null,
    'rel'       => $_GET['rel'] ?? null,
    'type'      => $_GET['type'] ?? null,
    'search'    => $_GET['search'] ?? null,
];

$isRelation = $table === 'relations';

$hasFilter = !empty($params['linkTo']) && !empty($params['equalTo']);
$hasSearch = !empty($params['linkTo']) && empty($params['search']);

match (true) {

    // Relaciones con filtro
    $isRelation && $hasFilter => GetController::GetRelDataFilter(
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

    // Relaciones sin filtro
    $isRelation => GetController::GetRelData(
        $params['rel'],
        $params['type'],
        $params['select'],
        $params['orderBy'],
        $params['orderMode'],
        $params['startAt'],
        $params['endAt'],
    ),

    // Búsqueda
    $hasSearch => GetController::GetDataSearch(
        $table,
        $params['select'],
        $params['linkTo'],
        $params['search'],
        $params['orderBy'],
        $params['orderMode'],
        $params['startAt'],
        $params['endAt'],
    ),

    // Filtro normal
    $hasFilter => GetController::GetDataFilter(
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
    default => GetController::GetData(
        $table,
        $params['select'],
        $params['orderBy'],
        $params['orderMode'],
        $params['startAt'],
        $params['endAt'],
    ),
};

exit();