<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Services\Formatter\ExceptionMessage;

abstract class AppException extends \RuntimeException
{
    public function __construct(
        public readonly ExceptionMessage $exceptionMessage,
        ?\Throwable $previousException = null
    ) {
        parent::__construct(
            $this->exceptionMessage->getMessage(),
            0,
            $previousException,
        );
    }
}
