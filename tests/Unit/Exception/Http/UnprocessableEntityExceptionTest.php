<?php

declare(strict_types=1);

namespace Tests\Unit\Exception\Http;

use App\Exceptions\Http\UnprocessableEntityException;
use Symfony\Component\HttpFoundation\Response;

class UnprocessableEntityExceptionTest extends HttpExceptionBaseTestCase
{
    protected function getHttpStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    protected function makeHttpException(): string
    {
        return UnprocessableEntityException::class;
    }
}
