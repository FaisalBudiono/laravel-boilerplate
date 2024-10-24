<?php

declare(strict_types=1);

namespace App\Services\Logger;

use App\Services\Logger\Message\ValueObject\LogMessage;

interface Logger
{
    public function emergency(LogMessage $log): void;
    public function alert(LogMessage $log): void;
    public function critical(LogMessage $log): void;
    public function error(LogMessage $log): void;
    public function warning(LogMessage $log): void;
    public function notice(LogMessage $log): void;
    public function info(LogMessage $log): void;
    public function debug(LogMessage $log): void;
}
