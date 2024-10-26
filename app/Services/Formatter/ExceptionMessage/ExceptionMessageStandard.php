<?php

declare(strict_types=1);

namespace App\Services\Formatter\ExceptionMessage;

use App\Services\Formatter\ExceptionMessage;
use Illuminate\Support\Collection;

readonly class ExceptionMessageStandard implements ExceptionMessage
{
    /**
     * @param array<string|int, mixed> $meta
     */
    public function __construct(
        public string $errorMessage,
        public string $errorCode,
        public array $meta = [],
    ) {}

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
