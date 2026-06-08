<?php

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Models\GetModel;

class GetController
{
    // Peticion Get sin filtro
    public static function GetData($table, $select, $orderBy, $orderMode)
    {
        $resp = GetModel::GetData($table, $select, $orderBy, $orderMode);
        new GetController()->FncResponse($resp);
    }

    //Peticion Get con filtro
    public static function GetDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode)
    {
        $resp = GetModel::GetDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode);
        new GetController()->FncResponse($resp);
    }

    // repuesta del controlador
    public function FncResponse($response)
    {
        if (!empty($response)) {
            $json = array(
                'status' => 200,
                'total' => count($response),
                'results' => $response,
            );
        } else {
            $json = array(
                'status' => 403,
                'results' => 'Not fount',
            );
        }
        echo json_encode($json, http_response_code($json['status']));
    }
}
