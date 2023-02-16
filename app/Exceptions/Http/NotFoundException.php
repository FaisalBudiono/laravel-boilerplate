<?php

namespace App\Exceptions\Http;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends AbstractHttpException
{
    protected function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
