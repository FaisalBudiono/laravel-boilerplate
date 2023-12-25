<?php

declare(strict_types=1);

namespace Tests\Unit\Exception\Http;

use App\Exceptions\Http\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class UnauthorizedExceptionTest extends HttpExceptionBaseTestCase
{
    protected function getHttpStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    protected function makeHttpException(): string
    {
        return UnauthorizedException::class;
    }
}
