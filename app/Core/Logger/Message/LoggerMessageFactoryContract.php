<?php

declare(strict_types=1);

namespace App\Core\Logger\Message;

use Stringable;
use Throwable;

interface LoggerMessageFactoryContract
{
    public function makeHTTPError(Throwable $e): Stringable;

    public function makeHTTPStart(
        string $message,
        array $input = [],
    ): Stringable;

    public function makeHTTPSuccess(
        string $message,
        array $meta,
    ): Stringable;
}
