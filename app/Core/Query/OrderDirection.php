<?php

namespace App\Core\Query;

enum OrderDirection: string
{
    case ASCENDING = 'asc';
    case DESCENDING = 'desc';
}
