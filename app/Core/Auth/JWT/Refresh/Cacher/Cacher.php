<?php

namespace App\Core\Auth\JWT\Refresh\Cacher;

use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;

interface Cacher
{
    public function deleteAllGenerations(string $tokenID): void;
    public function find(string $tokenID): RefreshTokenClaims;
    public function save(RefreshTokenClaims $refreshTokenClaims): void;
}
