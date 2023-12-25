<?php

declare(strict_types=1);

namespace App\Models\User\Enum;

enum UserExceptionCode: string
{
    case DUPLICATED = 'USER.EMAIL.DUPLICATED';
    case INVALID_CREDENTIAL = 'USER.INVALID_CREDENTIAL';
}
