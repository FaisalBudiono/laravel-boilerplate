<?php

namespace App\Core\Logger\Message;

use Exception;
use Stringable;

interface LoggerMessageFactoryContract
{
    public function makeHTTPError(Exception $e): Stringable;

    public function makeHTTPStart(
        string $message,
        array $input = [],
    ): Stringable;

    public function makeHTTPSuccess(
        string $message,
        array $meta,
    ): Stringable;
}
