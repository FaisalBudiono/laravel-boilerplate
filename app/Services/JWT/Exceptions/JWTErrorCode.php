<?php

declare(strict_types=1);

namespace App\Services\JWT\Exceptions;

enum JWTErrorCode: string
{
    case FAILED_DECODING = 'JWT.FAILED-DECODING';
    case GENERIC_INVALID = 'JWT.GENERIC-INVALID';
    case INVALID_SIGNATURE = 'JWT.INVALID-SIGNATURE';
    case MISSING_HEADER = 'JWT.MISSING-HEADER';
    case TIME_RELATED = 'JWT.TIME-RELATED';
}
