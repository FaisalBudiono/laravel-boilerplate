<?php

namespace Tests\Unit\Exception\Http;

use App\Exceptions\Http\NotFoundException;
use Symfony\Component\HttpFoundation\Response;

class NotFoundExceptionTest extends HttpExceptionBaseTestCase
{
    protected function getHttpStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    protected function makeHttpException(): string
    {
        return NotFoundException::class;
    }
}
