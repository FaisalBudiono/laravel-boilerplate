<?php

declare(strict_types=1);

namespace App\Services\Auth\Exceptions;

enum AuthErrorCode: string
{
    case LACK_OF_AUTHORIZATION = 'AUTH.LACK-OF-AUTHORIZATION';
    case REQUIRE_AUTHORIZATION = 'AUTH.REQUIRE';
}
