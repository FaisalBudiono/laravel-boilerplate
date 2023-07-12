<?php

namespace App\Core\Auth\JWT\Refresh\ValueObject;

use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use Carbon\Carbon;

class RefreshTokenClaims
{
    public function __construct(
        readonly public string $id,
        readonly public ClaimsUser $user,
        readonly public Carbon $expiredAt,
        public ?string $childID = null,
    ) {
    }
}
