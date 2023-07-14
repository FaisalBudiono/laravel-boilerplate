<?php

namespace App\Core\Auth;

use App\Core\Auth\JWT\ValueObject\TokenPair;
use App\Port\Core\Auth\LoginPort;
use App\Port\Core\Auth\LogoutPort;

interface AuthJWTCoreContract
{
    public function login(LoginPort $request): TokenPair;
    public function logout(LogoutPort $request): void;
}
