<?php

namespace App\Core\Logger\MessageFormatter;

class LoggerMessageFormatterGeneric implements LoggerMessageFormatter
{
    public function __construct(
        protected string $endpoint,
        protected string $requestID,
        protected ProcessingStatus $processingStatus,
        protected string $message,
        protected array $meta,
    ) {
        $this->meta = $meta;
    }

    public function getMessage(): string
    {
        return json_encode([
            'endpoint' => $this->endpoint,
            'request-id' => $this->requestID,
            'processing-status' => $this->processingStatus->value,
            'message' => $this->message,
            'meta' => $this->meta,
        ]);
    }
}
