<?php

declare(strict_types=1);

namespace App\Services\Logger\Message;

use App\Services\Logger\Message\Enum\ProcessingStatus;
use App\Services\Logger\Message\ValueObject\LogMessage;

class LogMessageBuilder implements LogMessageBuilderContract
{
    protected ?string $endpoint;
    protected ?string $requestID;
    protected ?string $ip;
    protected ?ProcessingStatus $processingStatus;
    protected ?string $message;
    protected ?array $meta;

    public function endpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function requestID(string $requestID): self
    {
        $this->requestID = $requestID;

        return $this;
    }

    public function ip(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function processingStatus(ProcessingStatus $processingStatus): self
    {
        $this->processingStatus = $processingStatus;

        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function meta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function build(): LogMessage
    {
        return new LogMessage(
            endpoint: $this->endpoint ?? '',
            requestID: $this->requestID ?? '',
            ip: $this->ip ?? '',
            processingStatus: $this->processingStatus ?? ProcessingStatus::BEGIN,
            message: $this->message ?? '',
            meta: $this->meta ?? [],
        );
    }
}
