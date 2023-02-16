<?php

namespace Tests\Unit\Exception\Http;

use App\Exceptions\Http\ConflictException;
use Symfony\Component\HttpFoundation\Response;

class ConflictExceptionTest extends HttpExceptionBaseTestCase
{
    protected function getHttpStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    protected function makeHttpException(): string
    {
        return ConflictException::class;
    }
}
