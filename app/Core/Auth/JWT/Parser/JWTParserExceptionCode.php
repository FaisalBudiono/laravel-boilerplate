<?php

declare(strict_types=1);

namespace App\Core\Auth\JWT\Parser;

enum JWTParserExceptionCode: string
{
    case FAILED_DECODING = 'JWT.FAILED-DECODING';
}
