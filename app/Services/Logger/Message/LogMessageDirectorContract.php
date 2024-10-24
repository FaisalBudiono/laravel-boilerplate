<?php

declare(strict_types=1);

namespace App\Services\Logger\Message;

use App\Services\Logger\Message\Enum\ProcessingStatus;

interface LogMessageDirectorContract
{
    public function buildHTTP(
        LogMessageBuilderContract $builder,
        ProcessingStatus $processingStatus,
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

    public function makeBuilder(): LogMessageBuilderContract;
}
