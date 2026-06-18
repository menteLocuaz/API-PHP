<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Http\Response;
use Arancamon\ApiPhp\Models\GetModel;
use Arancamon\ApiPhp\Models\PosModel;
use Arancamon\ApiPhp\Models\PutRepository;
use Arancamon\ApiPhp\Security\JwtService;
use Firebase\JWT\JWT;

class PosController
{
    private \Closure $jwtEncoder;

    public function __construct(
        private ?PosModel $posModel = null,
        private ?GetModel $getModel = null,
        private ?PutRepository $putRepository = null,
        ?\Closure $jwtEncoder = null,
    ) {
        $this->posModel ??= new PosModel;
        $this->getModel ??= new GetModel;
        $this->putRepository ??= new PutRepository;
        $this->jwtEncoder = $jwtEncoder ?? JWT::encode(...);
    }

    public function postData(string $table, array $data): void
    {
        $response = $this->posModel->postData($table, $data);
        $this->response($response);
    }

    public function postRegister(string $table, array $data, string $suffix): void
    {
        if (isset($data['password_' . $suffix]) && $data['password_' . $suffix] != null) {
            $crypt = crypt($data['password_' . $suffix], '$2a$07$azybxcags23425sdg23sdfhsd$');
            $data['password_' . $suffix] = $crypt;

            $response = $this->posModel->postData($table, $data);
            $this->response($response, null, $suffix);
        } else {
            $response = $this->posModel->postData($table, $data);

            if (isset($response['comment']) && $response['comment'] == 'The process was successful') {
                $getResponse = $this->getModel->findWithFilters(
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
                    $jwt = ($this->jwtEncoder)($token, 'dfhsdfg34dfchs4xgsrsdry46', 'HS256');

                    $update = $this->putRepository->update(
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

                        $this->response($getResponse, null, $suffix);
                    }
                }
            }
        }
    }

    public function postLogin(string $table, array $data, string $suffix): void
    {
        $response = $this->getModel->findWithFilters(
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
                    $jwt = ($this->jwtEncoder)($token, 'dfhsdfg34dfchs4xgsrsdry46', 'HS256');

                    $update = $this->putRepository->update(
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

                        $this->response($response, null, $suffix);
                    }
                } else {
                    $this->response(null, 'Wrong password', $suffix);
                }
            } else {
                $token = JwtService::jwt($response[0]->{'id_' . $suffix}, $response[0]->{'email_' . $suffix});
                $jwt = ($this->jwtEncoder)($token, 'dfhsdfg34dfchs4xgsrsdry46', 'HS256');

                $update = $this->putRepository->update(
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

                    $this->response($response, null, $suffix);
                }
            }
        } else {
            $this->response(null, 'Wrong email', $suffix);
        }
    }

    private function response(mixed $response, ?string $error = null, ?string $suffix = null): void
    {
        if (!empty($response)) {
            if ($suffix && isset($response[0]->{'password_' . $suffix})) {
                unset($response[0]->{'password_' . $suffix});
            }

            Response::json($response);
        } else {
            if ($error !== null) {
                Response::error($error);
            } else {
                Response::notFound('post');
            }
        }
    }
}
