<?php

declare(strict_types=1);

namespace App\Core\User\Enum;

enum UserExceptionCode: string
{
    case DUPLICATED = 'USER.EMAIL.DUPLICATED';
    case INVALID_CREDENTIAL = 'USER.INVALID_CREDENTIAL';
    case PERMISSION_INSUFFICIENT = 'USER.PERMISSION.INSUFFICIENT';
}
