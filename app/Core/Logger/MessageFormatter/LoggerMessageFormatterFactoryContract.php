<?php

namespace App\Core\Logger\MessageFormatter;

interface LoggerMessageFormatterFactoryContract
{
    public function makeGeneric(
        string $endpoint,
        string $requestID,
        ProcessingStatus $processingStatus,
        string $message,
        array $meta,
    ): LoggerMessageFormatter;
}
