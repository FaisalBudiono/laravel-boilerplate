<?php

declare(strict_types=1);

namespace App\Core\Query\Enum;

enum OrderDirection: string
{
    case ASCENDING = 'asc';
    case DESCENDING = 'desc';
}
