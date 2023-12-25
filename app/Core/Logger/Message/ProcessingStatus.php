<?php

declare(strict_types=1);

namespace App\Core\Logger\Message;

enum ProcessingStatus: string
{
    case BEGIN = "BEGIN";
    case ERROR = "ERROR";
    case PROCESSING = "PROCESSING";
    case SUCCESS = "SUCCESS";
}
