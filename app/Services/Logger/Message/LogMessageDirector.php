<?php

declare(strict_types=1);

namespace App\Services\Logger\Message;

use App\Exceptions\AppException;
use App\Http\Middleware\XRequestIDMiddleware;
use App\Services\Logger\Message\Enum\LogEndpoint;
use App\Services\Logger\Message\Enum\ProcessingStatus;
use Illuminate\Http\Request;

class LogMessageDirector implements LogMessageDirectorContract
{
    public function __construct(
        protected Request $request,
    ) {}

    public function buildHTTP(
        LogMessageBuilderContract $builder,
        ProcessingStatus $processingStatus,
    ): LogMessageBuilderContract {
        return $builder->endpoint($this->getRequestEndpoint())
            ->ip($this->getRequesterIP())
            ->requestID($this->getFormattedRequestID())
            ->processingStatus($processingStatus);
    }

    public function buildQueue(
        LogMessageBuilderContract $builder,
        ProcessingStatus $processingStatus,
        string $className,
        string $requestID,
    ): LogMessageBuilderContract {
        return $builder->endpoint(LogEndpoint::QUEUE->value)
            ->requestID($requestID)
            ->processingStatus($processingStatus)
            ->message($className);
    }

    public function buildForException(
        LogMessageBuilderContract $builder,
        \Throwable $e,
    ): LogMessageBuilderContract {
        return $builder->message($e->getMessage())
            ->meta([
                'detail' => $this->formatExceptionDetail($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ]);
    }

    public function makeBuilder(): LogMessageBuilderContract
    {
        return new LogMessageBuilder();
    }

    protected function formatExceptionDetail(\Throwable $e): ?array
    {
        if (!$e instanceof AppException) {
            return null;
        }

        return $e->exceptionMessage->getJsonResponse()->toArray();
    }

    protected function getFormattedRequestID(): string
    {
        $requestID = $this->request->header(XRequestIDMiddleware::HEADER_NAME);

        return is_array($requestID)
            ? implode(' ', $requestID)
            : $requestID ?? '';
    }

    protected function getRequestEndpoint(): string
    {
        return "{$this->request->method()} {$this->request->url()}";
    }

    protected function getRequesterIP(): string
    {
        return $this->request->ip() ?? '';
    }
}
