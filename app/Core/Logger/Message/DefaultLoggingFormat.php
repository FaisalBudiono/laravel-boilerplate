<?php

namespace App\Core\Logger\Message;

use Stringable;

abstract class DefaultLoggingFormat implements Stringable
{
    abstract protected function endpoint(): string;
    abstract protected function requestID(): string;
    abstract protected function processingStatus(): ProcessingStatus;
    abstract protected function message(): string;
    abstract protected function meta(): array;

    public function __toString(): string
    {
        return json_encode($this->getMessage());
    }

    public function getMessage(): array
    {
        return [
            'endpoint' => $this->endpoint(),
            'request-id' => $this->requestID(),
            'processing-status' => $this->processingStatus(),
            'message' => $this->message(),
            'meta' => $this->meta(),
        ];
    }
}
