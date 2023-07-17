<?php

namespace App\Core\Auth\JWT\Refresh;

enum RefreshTokenExceptionCode: string
{
    case EXPIRED = 'REFRESH-TOKEN.EXPIRED';
    case NOT_FOUND = 'REFRESH-TOKEN.NOT_FOUND';
    case TOKEN_IS_USED = 'REFRESH-TOKEN.TOKEN_IS_USED';
}
