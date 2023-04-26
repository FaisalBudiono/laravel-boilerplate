<?php

namespace App\Core\User\Query;

enum UserOrderBy: string
{
    case NAME = 'name';
    case EMAIL = 'email';
    case CREATED_AT = 'created_at';
}
