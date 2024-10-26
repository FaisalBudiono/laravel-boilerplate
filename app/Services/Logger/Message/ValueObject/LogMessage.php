<?php

declare(strict_types=1);

namespace App\Services\Logger\Message\ValueObject;

use App\Services\Logger\Message\Enum\ProcessingStatus;

readonly class LogMessage
{
    /**
     * @param array<string|int, mixed> $meta
     */
    public function __construct(
        public readonly string $endpoint,
        public readonly string $requestID,
        public readonly string $ip,
        public readonly ProcessingStatus $processingStatus,
        public readonly string $message,
        public readonly array $meta,
    ) {}
}
