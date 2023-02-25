<?php

namespace App\Exceptions\Core;

use App\Core\Formatter\ExceptionMessage\ExceptionMessage;
use Exception;

abstract class BaseException extends Exception
{
    public function __construct(
        public readonly ExceptionMessage $exceptionMessage,
        ?\Throwable $previousException = null
    ) {
        parent::__construct(
            $this->exceptionMessage->getMessage(),
            0,
            $previousException
        );
    }
}
