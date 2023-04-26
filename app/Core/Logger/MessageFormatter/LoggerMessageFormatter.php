<?php

namespace App\Core\Logger\MessageFormatter;

interface LoggerMessageFormatter
{
    public function getMessage(): string;
}
