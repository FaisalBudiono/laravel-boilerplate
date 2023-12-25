<?php

declare(strict_types=1);

namespace App\Core\Formatter\ExceptionMessage;

use App\Core\Formatter\ExceptionErrorCode;
use Illuminate\Support\Collection;

class ExceptionMessageGeneric implements ExceptionMessage
{
    protected ExceptionMessage $exceptionMessage;

    public function __construct()
    {
        $this->exceptionMessage = new ExceptionMessageStandard(
            'Something Wrong on Our Server',
            ExceptionErrorCode::GENERIC->value
        );
    }

    public function getJsonResponse(): Collection
    {
        return $this->exceptionMessage->getJsonResponse();
    }

    public function getMessage(): string
    {
        return $this->exceptionMessage->getMessage();
    }
}
