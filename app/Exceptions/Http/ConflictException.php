<?php

namespace App\Exceptions\Http;

use Symfony\Component\HttpFoundation\Response;

class ConflictException extends AbstractHttpException
{
    protected function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
