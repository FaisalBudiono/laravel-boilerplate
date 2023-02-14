<?php

namespace App\Core\User;

use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;

interface UserCoreInterface
{
    public function create(CreateUserPort $request): User;
}
