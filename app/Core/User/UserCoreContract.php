<?php

namespace App\Core\User;

use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;
use App\Port\Core\User\GetUserPort;

interface UserCoreContract
{
    public function create(CreateUserPort $request): User;
    public function get(GetUserPort $request): User;
}
