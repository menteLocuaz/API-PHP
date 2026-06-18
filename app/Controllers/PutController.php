<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Controllers;

use Arancamon\ApiPhp\Database\Exceptions\DatabaseException;
use Arancamon\ApiPhp\Http\Response;
use Arancamon\ApiPhp\Models\PutRepository;

class PutController
{
    private PutRepository $putRepository;

    public function __construct(?PutRepository $putRepository = null)
    {
        $this->putRepository = $putRepository ?? new PutRepository;
    }

    public function putData(string $table, array $data, mixed $id, string $nameId): void
    {
        try {
            $response = $this->putRepository->update($table, $data, $id, $nameId);
            $this->response($response);
        } catch (DatabaseException $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    private function response(?array $response): void
    {
        if (!empty($response)) {
            Response::json($response);
        } else {
            Response::notFound('put');
        }
    }
}
