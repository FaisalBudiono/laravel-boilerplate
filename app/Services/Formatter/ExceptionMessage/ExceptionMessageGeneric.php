<?php

declare(strict_types=1);

namespace App\Services\Formatter\ExceptionMessage;

use App\Services\Formatter\ExceptionErrorCode;
use App\Services\Formatter\ExceptionMessage;
use Illuminate\Support\Collection;

readonly class ExceptionMessageGeneric implements ExceptionMessage
{
    protected ExceptionMessage $message;

    public function __construct()
    {
        $this->message = new ExceptionMessageStandard(
            'Something Wrong on Our Server',
            ExceptionErrorCode::GENERIC->value,
        );
    }

    public function getJsonResponse(): Collection
    {
        return $this->message->getJsonResponse();
    }

    public function getMessage(): string
    {
        return $this->message->getMessage();
    }
}
