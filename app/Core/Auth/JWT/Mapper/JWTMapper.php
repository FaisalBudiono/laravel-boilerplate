<?php

namespace App\Core\Auth\JWT\Mapper;

use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Models\User\User;

class JWTMapper implements JWTMapperContract
{
    public function map(User $user): Claims
    {
        return new Claims(
            new ClaimsUser($user->id, $user->email),
            collect($this->getAudience()),
            now()->subSecond(),
            now()->subSecond(),
            now()->addSeconds($this->getTTLInSeconds()),
        );
    }

    protected function getAudience(): array
    {
        return config('jwt.audience');
    }

    protected function getTTLInSeconds(): int
    {
        return config('jwt.ttl');
    }
}
