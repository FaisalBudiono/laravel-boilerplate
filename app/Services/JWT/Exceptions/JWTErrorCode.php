<?php

declare(strict_types=1);

namespace App\Services\JWT\Exceptions;

enum JWTErrorCode: string
{
    case FAILED_DECODING = 'JWT.FAILED-DECODING';
    case MISSING_HEADER = 'JWT.MISSING-HEADER';
    case INVALID_SIGNATURE = 'JWT.INVALID-SIGNATURE';
    case TIME_RELATED = 'JWT.TIME-RELATED';
}
