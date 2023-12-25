<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Core\Formatter\ExceptionMessage\ExceptionMessage;
use Exception;

abstract class BaseException extends Exception
{
    public function __construct(
        public readonly ExceptionMessage $exceptionMessage,
        ?\Throwable $previousException = null
    ) {
        parent::__construct(
            $this->exceptionMessage->getJsonResponse()->toJson(),
            0,
            $previousException
        );
    }
}
