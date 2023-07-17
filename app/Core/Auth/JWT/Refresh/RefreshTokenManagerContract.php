<?php

namespace App\Core\Auth\JWT\Refresh;

use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Models\User\User;

interface RefreshTokenManagerContract
{
    public function create(User $user): RefreshTokenClaims;
    public function invalidate(string $tokenID): void;
    public function refresh(string $tokenID): RefreshTokenClaims;
}
