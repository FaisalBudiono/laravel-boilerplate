<?php

namespace App\Exceptions\Http;

use Symfony\Component\HttpFoundation\Response;

class InternalServerErrorException extends AbstractHttpException
{
    protected function getStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
