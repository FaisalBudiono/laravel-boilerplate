<?php

declare(strict_types=1);

namespace App\Core\Logger\Message;

use App\Exceptions\BaseException;
use App\Http\Middleware\XRequestIDMiddleware;
use Illuminate\Http\Request;
use Throwable;

class LogMessageDirector implements LogMessageDirectorContract
{
    public function __construct(
        protected Request $request,
    ) {
    }

    public function buildBegin(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract {
        return $this->setHTTPMeta($builder)
            ->processingStatus(ProcessingStatus::BEGIN);
    }

    public function buildProcessing(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract {
        return $this->setHTTPMeta($builder)
            ->processingStatus(ProcessingStatus::PROCESSING);
    }

    public function buildSuccess(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract {
        return $this->setHTTPMeta($builder)
            ->processingStatus(ProcessingStatus::SUCCESS);
    }

    public function buildError(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract {
        return $this->setHTTPMeta($builder)
            ->processingStatus(ProcessingStatus::ERROR);
    }

    public function buildHTTP(
        LogMessageBuilderContract $builder,
        ProcessingStatus $processingStatus,
    ): LogMessageBuilderContract {
        return $this->setHTTPMeta($builder)
            ->processingStatus($processingStatus);
    }

    public function buildEndpointHTTP(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract {
        return $this->setDefaultEndpoint($builder);
    }

    public function buildIPHTTP(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract {
        return $this->setIP($builder);
    }

    public function buildForException(
        LogMessageBuilderContract $builder,
        Throwable $e,
    ): LogMessageBuilderContract {
        return $builder->message($e->getMessage())
            ->meta([
                'detail' => $this->formatExceptionDetail($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ]);
    }

    protected function formatExceptionDetail(Throwable $e): ?array
    {
        if (!$e instanceof BaseException) {
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

    protected function setDefaultEndpoint(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract {
        return $builder->endpoint(
            "{$this->request->method()} {$this->request->url()}"
        );
    }

    protected function setIP(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract {
        return $builder->ip(
            $this->request->ip() ?? '',
        );
    }

    protected function setRequestID(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract {
        return $builder->requestID($this->getFormattedRequestID());
    }

    protected function setHTTPMeta(
        LogMessageBuilderContract $builder,
    ): LogMessageBuilderContract {
        return $this->setIP(
            $this->setRequestID(
                $this->setDefaultEndpoint($builder),
            ),
        );
    }
}
