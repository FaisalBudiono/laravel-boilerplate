<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User\User;
use App\Port\Services\User\GetDetailPort;

interface UserServiceContract
{
    public function getDetail(GetDetailPort $port): User;
}
