<?php

declare(strict_types=1);

namespace App\Exceptions\Http;

use Symfony\Component\HttpFoundation\Response;

class UnprocessableEntityException extends AbstractHttpException
{
    protected function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
