<?php

use Arancamon\ApiPhp\Controllers\GetController;

$table = explode('?', $routesArray[0])[0];
$select = $_GET['select'] ?? '*';
$orderBy = $_GET['orderBy'] ?? null;
$orderMode = $_GET['orderMode'] ?? null;

$reponse = new GetController();
// busqueda con filtro
if (isset($_GET['linkTo']) && isset($_GET['equalTo'])) {
    // Consulta con filtro
    $reponse->GetDataFilter($table, $select, $_GET['linkTo'], $_GET['equalTo'], $orderBy, $orderMode);
} else {
    // Consulta sin filtro
    $reponse->GetData($table, $select, $orderBy, $orderMode);
}
exit;
