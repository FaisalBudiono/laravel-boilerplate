<?php

namespace App\Core\Auth;

use App\Core\Auth\JWT\ValueObject\TokenPair;
use App\Port\Core\Auth\LoginPort;

interface AuthJWTCoreContract
{
    public function login(LoginPort $request): TokenPair;
}
