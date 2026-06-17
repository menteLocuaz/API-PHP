<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Models\GetModel;
use Arancamon\ApiPhp\Models\PosModel;
use Arancamon\ApiPhp\Models\PutModel;
use Arancamon\ApiPhp\Security\JwtService;
use Firebase\JWT\JWT;

class PosController
{
    public static function postData(string $table, array $data): void
    {
        $response = PosModel::postData($table, $data);

        $return = new self();
        $return->fncResponse($response, null, null);
    }

    public static function postRegister(string $table, array $data, string $suffix): void
    {
        if (isset($data['password_' . $suffix]) && $data['password_' . $suffix] != null) {
            $crypt = crypt($data['password_' . $suffix], '$2a$07$azybxcags23425sdg23sdfhsd$');

            $data['password_' . $suffix] = $crypt;

            $response = PosModel::postData($table, $data);

            $return = new self();
            $return->fncResponse($response, null, $suffix);
        } else {
            $response = PosModel::postData($table, $data);

            if (isset($response['comment']) && $response['comment'] == 'The process was successful') {
                $getResponse = GetModel::findWithFilters(
                    $table,
                    '*',
                    'email_' . $suffix,
                    $data['email_' . $suffix],
                    null,
                    null,
                    null,
                    null,
                );

                if (!empty($getResponse)) {
                    $token = JwtService::jwt($getResponse[0]->{'id_' . $suffix}, $getResponse[0]->{'email_' . $suffix});

                    $jwt = JWT::encode($token, 'dfhsdfg34dfchs4xgsrsdry46', 'HS256');

                    $update = PutModel::putData(
                        $table,
                        [
                            'token_' . $suffix => $jwt,
                            'token_exp_' . $suffix => $token['exp'],
                        ],
                        $getResponse[0]->{'id_' . $suffix},
                        'id_' . $suffix,
                    );

                    if (isset($update['comment']) && $update['comment'] == 'The process was successful') {
                        $getResponse[0]->{'token_' . $suffix} = $jwt;
                        $getResponse[0]->{'token_exp_' . $suffix} = $token['exp'];

                        $return = new self();
                        $return->fncResponse($getResponse, null, $suffix);
                    }
                }
            }
        }
    }

    public static function postLogin(string $table, array $data, string $suffix): void
    {
        $response = GetModel::findWithFilters(
            $table,
            '*',
            'email_' . $suffix,
            $data['email_' . $suffix],
            null,
            null,
            null,
            null,
        );

        if (!empty($response)) {
            if ($response[0]->{'password_' . $suffix} != null) {
                $crypt = crypt($data['password_' . $suffix], '$2a$07$azybxcags23425sdg23sdfhsd$');

                if ($response[0]->{'password_' . $suffix} == $crypt) {
                    $token = JwtService::jwt($response[0]->{'id_' . $suffix}, $response[0]->{'email_' . $suffix});

                    $jwt = JWT::encode($token, 'dfhsdfg34dfchs4xgsrsdry46', 'HS256');

                    $update = PutModel::putData(
                        $table,
                        [
                            'token_' . $suffix => $jwt,
                            'token_exp_' . $suffix => $token['exp'],
                        ],
                        $response[0]->{'id_' . $suffix},
                        'id_' . $suffix,
                    );

                    if (isset($update['comment']) && $update['comment'] == 'The process was successful') {
                        $response[0]->{'token_' . $suffix} = $jwt;
                        $response[0]->{'token_exp_' . $suffix} = $token['exp'];

                        $return = new self();
                        $return->fncResponse($response, null, $suffix);
                    }
                } else {
                    $return = new self();
                    $return->fncResponse(null, 'Wrong password', $suffix);
                }
            } else {
                $token = JwtService::jwt($response[0]->{'id_' . $suffix}, $response[0]->{'email_' . $suffix});

                $jwt = JWT::encode($token, 'dfhsdfg34dfchs4xgsrsdry46', 'HS256');

                $update = PutModel::putData(
                    $table,
                    [
                        'token_' . $suffix => $jwt,
                        'token_exp_' . $suffix => $token['exp'],
                    ],
                    $response[0]->{'id_' . $suffix},
                    'id_' . $suffix,
                );

                if (isset($update['comment']) && $update['comment'] == 'The process was successful') {
                    $response[0]->{'token_' . $suffix} = $jwt;
                    $response[0]->{'token_exp_' . $suffix} = $token['exp'];

                    $return = new self();
                    $return->fncResponse($response, null, $suffix);
                }
            }
        } else {
            $return = new self();
            $return->fncResponse(null, 'Wrong email', $suffix);
        }
    }

    public function fncResponse(mixed $response, ?string $error, ?string $suffix): void
    {
        if (!empty($response)) {
            if (isset($response[0]->{'password_' . $suffix})) {
                unset($response[0]->{'password_' . $suffix});
            }

            $json = [
                'status' => 200,
                'results' => $response,
            ];
        } else {
            if ($error != null) {
                $json = [
                    'status' => 400,
                    'results' => $error,
                ];
            } else {
                $json = [
                    'status' => 404,
                    'results' => 'Not Found',
                    'method' => 'post',
                ];
            }
        }

        http_response_code($json['status']);
        header('Content-Type: application/json');
        echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
