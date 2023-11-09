<?php

namespace App\Core\Query\Enum;

enum OrderDirection: string
{
    case ASCENDING = 'asc';
    case DESCENDING = 'desc';
}
