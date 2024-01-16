<?php

declare(strict_types=1);

namespace App\Core\Logger\Message;

use App\Core\Logger\Message\ValueObject\LogMessage;

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
            $this->endpoint ?? '',
            $this->requestID ?? '',
            $this->ip ?? '',
            $this->processingStatus ?? ProcessingStatus::BEGIN,
            $this->message ?? '',
            $this->meta ?? [],
        );
    }
}
