<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Port\Services\Auth\LoginPort;
use App\Port\Services\Auth\LogoutPort;
use App\Port\Services\Auth\RefreshTokenPort;
use App\Services\Auth\ValueObject\Token;

interface AuthServiceContract
{
    public function login(LoginPort $port): Token;
    public function logout(LogoutPort $port): void;
    public function refreshToken(RefreshTokenPort $port): Token;
}
