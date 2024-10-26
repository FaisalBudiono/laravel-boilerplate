<?php

declare(strict_types=1);

namespace App\Services\Logger;

use App\Services\Logger\Message\ValueObject\LogMessage;
use Illuminate\Support\Facades\Log;

class LoggerLaravel implements Logger
{
    public function emergency(LogMessage $log): void
    {
        Log::emergency($log->message, $this->toArray($log));
    }

    public function alert(LogMessage $log): void
    {
        Log::alert($log->message, $this->toArray($log));
    }

    public function critical(LogMessage $log): void
    {
        Log::critical($log->message, $this->toArray($log));
    }

    public function error(LogMessage $log): void
    {
        Log::error($log->message, $this->toArray($log));
    }

    public function warning(LogMessage $log): void
    {
        Log::warning($log->message, $this->toArray($log));
    }

    public function notice(LogMessage $log): void
    {
        Log::notice($log->message, $this->toArray($log));
    }

    public function info(LogMessage $log): void
    {
        Log::info($log->message, $this->toArray($log));
    }

    public function debug(LogMessage $log): void
    {
        Log::debug($log->message, $this->toArray($log));
    }

    /**
     * @return array<string, mixed>
     */
    protected function toArray(LogMessage $log): array
    {
        return [
            'endpoint' => $log->endpoint,
            'request-id' => $log->requestID,
            'ip' => $log->ip,
            'processing-status' => $log->processingStatus->value,
            'message' => $log->message,
            'meta' => $log->meta,
        ];
    }
}
