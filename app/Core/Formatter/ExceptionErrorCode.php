<?php

namespace App\Core\Formatter;

enum ExceptionErrorCode: string
{
    case GENERIC = 'GENERIC';
    case INVALID_VALIDATION = 'INVALID-STRUCTURE-VALIDATION';
    case REQUIRE_AUTHORIZATION = 'REQUIRE-AUTH';
}
