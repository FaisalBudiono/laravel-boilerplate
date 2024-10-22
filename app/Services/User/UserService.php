<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Port\Services\User\GetDetailPort;
use App\Models\User\User;

class UserService implements UserServiceContract
{
    public function getDetail(GetDetailPort $port): User
    {
        return $port->getLoggedInUser();
    }
}
