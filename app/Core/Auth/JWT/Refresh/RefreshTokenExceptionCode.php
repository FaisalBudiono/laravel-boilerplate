<?php

namespace App\Core\Auth\JWT\Refresh;

enum RefreshTokenExceptionCode: string
{
    case EXPIRED = 'REFRESH-TOKEN.EXPIRED';
    case TOKEN_IS_USED = 'REFRESH-TOKEN.TOKEN_IS_USED';
}
