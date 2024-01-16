<?php

declare(strict_types=1);

namespace App\Core\Logger\Message;

use App\Core\Logger\Message\ValueObject\LogMessage;

interface LogMessageBuilderContract
{
    public function endpoint(string $endpoint): self;
    public function requestID(string $requestID): self;
    public function ip(string $ip): self;
    public function processingStatus(ProcessingStatus $processingStatus): self;
    public function message(string $message): self;
    public function meta(array $meta): self;

    public function build(): LogMessage;
}
