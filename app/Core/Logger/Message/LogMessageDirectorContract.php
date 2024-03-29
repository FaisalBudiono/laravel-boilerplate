<?php

declare(strict_types=1);

namespace App\Core\Logger\Message;

interface LogMessageDirectorContract
{
    public function buildHTTP(
        LogMessageBuilderContract $builder,
        ProcessingStatus $processingStatus,
    ): LogMessageBuilderContract;

    public function buildEndpointHTTP(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract;
    public function buildIPHTTP(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract;

    public function buildQueue(
        LogMessageBuilderContract $builder,
        ProcessingStatus $processingStatus,
        string $className,
        string $requestID,
    ): LogMessageBuilderContract;

    public function buildForException(
        LogMessageBuilderContract $builder,
        \Throwable $e,
    ): LogMessageBuilderContract;
}
