<?php

declare(strict_types=1);

namespace App\Core\Auth\JWT\Refresh\Mapper;

use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Models\User\User;

interface UserTokenMapperContract
{
    public function map(User $user): RefreshTokenClaims;
}
