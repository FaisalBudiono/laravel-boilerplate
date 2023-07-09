<?php

namespace App\Core\Auth\JWT\Parser;

enum JWTParserExceptionCode: string
{
    case FAILED_DECODING = 'FAILED-DECODING';
}
