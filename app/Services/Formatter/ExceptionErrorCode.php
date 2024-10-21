<?php

declare(strict_types=1);

namespace App\Services\Formatter;

enum ExceptionErrorCode: string
{
    case GENERIC = 'GENERIC';
    case RESOURCE_NOT_FOUND = 'RESOURCE-NOT-FOUND';
}
