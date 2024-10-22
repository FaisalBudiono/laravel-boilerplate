<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Port\Services\Auth\LoginPort;
use App\Services\Auth\ValueObject\Token;

interface AuthServiceContract
{
    public function login(LoginPort $port): Token;
}
