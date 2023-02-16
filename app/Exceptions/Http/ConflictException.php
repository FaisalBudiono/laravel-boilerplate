<?php

namespace App\Exceptions\Http;

use App\Core\Formatter\ExceptionMessage\ExceptionMessage;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ConflictException extends RuntimeException
{
    public function __construct(
        protected ExceptionMessage $exceptionMessage,
        ?Throwable $previousException = null
    ) {
        parent::__construct(
            $this->exceptionMessage->getMessage(),
            $this->getStatusCode(),
            $previousException
        );
    }

    public function render($request)
    {
        return response()->json(
            $this->exceptionMessage->getJsonResponse(),
            $this->getStatusCode()
        );
    }

    protected function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
