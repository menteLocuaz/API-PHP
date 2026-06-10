<?php

use Arancamon\ApiPhp\Controllers\GetController;

$table = explode('?', $routesArray[0])[0];
$select = $_GET['select'] ?? '*';
$orderBy = $_GET['orderBy'] ?? null;
$orderMode = $_GET['orderMode'] ?? null;
$startAt = $_GET['startAt'] ?? null;
$endAt = $_GET['endAt'] ?? null;

$reponse = new GetController();
// busqueda con filtro
if (isset($_GET['linkTo']) && isset($_GET['equalTo'])) {
    // Consulta con filtro
    $reponse->GetDataFilter($table, $select, $_GET['linkTo'], $_GET['equalTo'], $orderBy, $orderMode, $startAt, $endAt);
} elseif (
    isset($_GET['rel'])
    && isset($_GET['type'])
    && $table == 'relations'
    && !isset($_GET['linkTo'])
    && !isset($_GET['equalTo'])
) {
    $reponse->GetRelData($_GET['rel'], $_GET['type'], $select, $orderBy, $orderMode, $startAt, $endAt);
} else {
    // Consulta sin filtro
    $reponse->GetData($table, $select, $orderBy, $orderMode, $startAt, $endAt);
}
exit();
