<?php

declare(strict_types=1);

namespace App\Services\Logger\Message;

use App\Services\Logger\Message\Enum\ProcessingStatus;
use App\Services\Logger\Message\ValueObject\LogMessage;

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
