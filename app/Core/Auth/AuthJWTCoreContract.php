<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Auth\JWT\ValueObject\TokenPair;
use App\Port\Core\Auth\GetRefreshTokenPort;
use App\Port\Core\Auth\LoginPort;
use App\Port\Core\Auth\LogoutPort;

interface AuthJWTCoreContract
{
    public function login(LoginPort $request): TokenPair;
    public function logout(LogoutPort $request): void;
    public function refresh(GetRefreshTokenPort $request): TokenPair;
}
