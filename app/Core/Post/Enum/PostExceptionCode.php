<?php

declare(strict_types=1);

namespace App\Core\Post\Enum;

enum PostExceptionCode: string
{
    case PERMISSION_INSUFFICIENT = 'POST.PERMISSION.INSUFFICIENT';
}
