<?php

namespace App\Core\Auth\JWT\Signer;

enum JWTSignerExceptionCode: string
{
    case INVALID_SIGNATURE = 'JWT.INVALID-SIGNATURE';
    case TIME_RELATED = 'JWT.TIME-RELATED';
}
