<?php

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
            Str::uuid(),
            new RefreshTokenClaimsUser($user->id, $user->email),
            now()->addMinutes(config('jwt.refresh.ttl')),
        );
    }
}
