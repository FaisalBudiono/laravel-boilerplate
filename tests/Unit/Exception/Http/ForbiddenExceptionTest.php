<?php

declare(strict_types=1);

namespace Tests\Unit\Exception\Http;

use App\Exceptions\Http\ForbiddenException;
use Symfony\Component\HttpFoundation\Response;

class ForbiddenExceptionTest extends HttpExceptionBaseTestCase
{
    protected function getHttpStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }

    protected function makeHttpException(): string
    {
        return ForbiddenException::class;
    }
}
