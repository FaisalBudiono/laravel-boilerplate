<?php

namespace App\Models\User\Enum;


enum UserExceptionCode: string
{
    case DUPLICATED = 'USER.EMAIL.DUPLICATED';
}
