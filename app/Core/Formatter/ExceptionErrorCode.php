<?php

declare(strict_types=1);

namespace App\Core\Formatter;

enum ExceptionErrorCode: string
{
    case AUTHENTICATION_NEEDED = 'AUTHENTICATION-NEEDED';
    case GENERIC = 'GENERIC';
    case INVALID_VALIDATION = 'INVALID-STRUCTURE-VALIDATION';
    case LACK_OF_AUTHORIZATION = 'LACK-OF-AUTHORIZATION';
    case MODEL_NOT_FOUND = 'MODEL-NOT-FOUND';
    case REQUIRE_AUTHORIZATION = 'REQUIRE-AUTH';
}
