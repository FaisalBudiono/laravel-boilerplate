<?php

namespace App\Core\Logger\MessageFormatter;

enum ProcessingStatus: string
{
    case BEGIN = "BEGIN";
    case ERROR = "ERROR";
    case PROCESSING = "PROCESSING";
    case SUCCESS = "SUCCESS";
}
