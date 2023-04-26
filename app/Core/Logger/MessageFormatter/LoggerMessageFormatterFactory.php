<?php

namespace App\Core\Logger\MessageFormatter;

class LoggerMessageFormatterFactory implements LoggerMessageFormatterFactoryContract
{
    public function makeGeneric(
        string $endpoint,
        string $requestID,
        ProcessingStatus $processingStatus,
        string $message,
        array $meta,
    ): LoggerMessageFormatter {
        return new LoggerMessageFormatterGeneric(
            $endpoint,
            $requestID,
            $processingStatus,
            $message,
            $meta,
        );
    }
}
