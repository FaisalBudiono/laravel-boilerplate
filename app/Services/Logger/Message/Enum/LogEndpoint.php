<?php

declare(strict_types=1);

namespace App\Services\Logger\Message\Enum;

enum LogEndpoint: string
{
    case QUEUE = 'queue';
}
