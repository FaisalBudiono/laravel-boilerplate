<?php

declare(strict_types=1);

namespace App\Core\Formatter\ExceptionMessage;

use Illuminate\Support\Collection;

class ExceptionMessageStandard implements ExceptionMessage
{
    public function __construct(
        protected string $errorMessage,
        protected string $errorCode,
        protected ?array $meta = [],
    ) {
    }

    public function getJsonResponse(): Collection
    {
        return collect([
            'message' => $this->errorMessage,
            'errorCode' => $this->errorCode,
            'meta' => $this->meta,
        ]);
    }

    public function getMessage(): string
    {
        return $this->errorMessage;
    }
}
