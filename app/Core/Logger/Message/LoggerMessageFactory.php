<?php

declare(strict_types=1);

namespace App\Core\Logger\Message;

use Illuminate\Http\Request;
use Stringable;
use Throwable;

class LoggerMessageFactory implements LoggerMessageFactoryContract
{
    public function __construct(
        protected Request $request,
    ) {
    }

    public function makeHTTPError(Throwable $e): Stringable
    {
        return new LoggingHTTPError(
            $this->request,
            $e->getMessage(),
            [
                'trace' => $e->getTrace(),
            ],
        );
    }

    public function makeHTTPStart(string $message, array $input = []): Stringable
    {
        return new LoggingHTTPStart($this->request, $message, [
            'input' => $input,
        ]);
    }

    public function makeHTTPSuccess(string $message, array $meta): Stringable
    {
        return new LoggingHTTPSuccess($this->request, $message, $meta);
    }
}
