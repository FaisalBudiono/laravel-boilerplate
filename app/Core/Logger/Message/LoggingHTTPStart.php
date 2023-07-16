<?php

namespace App\Core\Logger\Message;

use App\Http\Middleware\XRequestIDMiddleware;
use Illuminate\Http\Request;

class LoggingHTTPStart extends DefaultLoggingFormat
{
    public function __construct(
        protected Request $request,
        protected string $message,
        protected array $meta,
    ) {
    }

    protected function endpoint(): string
    {
        return $this->request->url();
    }

    protected function requestID(): string
    {
        return $this->request->header(XRequestIDMiddleware::HEADER_NAME);
    }

    protected function processingStatus(): ProcessingStatus
    {
        return ProcessingStatus::BEGIN;
    }

    protected function message(): string
    {
        return $this->message;
    }

    protected function meta(): array
    {
        return $this->meta;
    }
}
