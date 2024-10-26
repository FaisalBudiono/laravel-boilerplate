<?php

declare(strict_types=1);

namespace App\Exceptions\Http;

use App\Services\Formatter\ExceptionMessage;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractHttpException extends \RuntimeException
{
    public function __construct(
        protected ExceptionMessage $exceptionMessage,
        ?\Throwable $previousException = null
    ) {
        parent::__construct(
            $this->exceptionMessage->getMessage(),
            $this->getStatusCode(),
            $previousException,
        );
    }

    /**
     * @param mixed $request
     */
    public function render($request): Response
    {
        return response()->json(
            [
                'errors' => $this->exceptionMessage->getJsonResponse(),
            ],
            $this->getStatusCode(),
        );
    }

    abstract protected function getStatusCode(): int;
}
