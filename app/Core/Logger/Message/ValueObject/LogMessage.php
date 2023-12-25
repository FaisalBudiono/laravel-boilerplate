<?php

declare(strict_types=1);

namespace App\Core\Logger\Message\ValueObject;

use App\Core\Logger\Message\ProcessingStatus;
use Stringable;

class LogMessage implements Stringable
{
    public function __construct(
        protected string $endpoint,
        protected string $requestID,
        protected ProcessingStatus $processingStatus,
        protected string $message,
        protected array $meta,
    ) {
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    public function toArray(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'request-id' => $this->requestID,
            'processing-status' => $this->processingStatus->value,
            'message' => $this->message,
            'meta' => $this->meta,
        ];
    }
}
