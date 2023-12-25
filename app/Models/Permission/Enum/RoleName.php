<?php

declare(strict_types=1);

namespace App\Models\Permission\Enum;

enum RoleName: string
{
    case ADMIN = 'admin';
    case NORMAL = 'normal';
}
