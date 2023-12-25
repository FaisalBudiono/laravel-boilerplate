<?php

declare(strict_types=1);

namespace App\Exceptions\Http;

use Symfony\Component\HttpFoundation\Response;

class ForbiddenException extends AbstractHttpException
{
    protected function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
