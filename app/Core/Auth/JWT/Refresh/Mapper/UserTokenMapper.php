<?php

declare(strict_types=1);

namespace App\Core\Auth\JWT\Refresh\Mapper;

use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Models\User\User;
use Illuminate\Support\Str;

class UserTokenMapper implements UserTokenMapperContract
{
    public function map(User $user): RefreshTokenClaims
    {
        return new RefreshTokenClaims(
            Str::uuid()->toString(),
            new RefreshTokenClaimsUser((string)$user->id, $user->email),
            now()->addSeconds($this->getJWTRefreshTokenTTLInSeconds()),
        );
    }

    protected function getJWTRefreshTokenTTLInSeconds(): int
    {
        return intval(config('jwt.refresh.ttl'));
    }
}
