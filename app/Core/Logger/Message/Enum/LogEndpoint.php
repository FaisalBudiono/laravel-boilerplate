<?php

declare(strict_types=1);

namespace App\Core\Logger\Message\Enum;

enum LogEndpoint: string
{
    case QUEUE = 'queue';
}
