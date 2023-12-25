<?php

declare(strict_types=1);

namespace Tests\Unit\Exception\Http;

use App\Exceptions\Http\InternalServerErrorException;
use Symfony\Component\HttpFoundation\Response;

class InternalServerErrorExceptionTest extends HttpExceptionBaseTestCase
{
    protected function getHttpStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    protected function makeHttpException(): string
    {
        return InternalServerErrorException::class;
    }
}
