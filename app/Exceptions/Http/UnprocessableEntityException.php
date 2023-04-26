<?php

namespace App\Exceptions\Http;

use Symfony\Component\HttpFoundation\Response;

class UnprocessableEntityException extends AbstractHttpException
{
    protected function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
