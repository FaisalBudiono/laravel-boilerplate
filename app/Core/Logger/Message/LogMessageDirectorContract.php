<?php

declare(strict_types=1);

namespace App\Core\Logger\Message;

interface LogMessageDirectorContract
{
    public function buildBegin(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract;
    public function buildProcessing(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract;
    public function buildSuccess(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract;
    public function buildError(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract;

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

    public function buildForException(
        LogMessageBuilderContract $builder,
        \Throwable $e,
    ): LogMessageBuilderContract;
}
