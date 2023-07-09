<?php

namespace App\Core\Auth\JWT\Signer;

enum JWTSignerExceptionCode: string
{
    case INVALID_SIGNATURE = 'INVALID-SIGNATURE';
    case TIME_RELATED = 'TIME-RELATED';
}
