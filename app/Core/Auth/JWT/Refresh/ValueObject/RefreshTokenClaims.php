<?php

namespace App\Core\Auth\JWT\Refresh\ValueObject;

use Carbon\Carbon;

class RefreshTokenClaims
{
    public function __construct(
        readonly public string $id,
        readonly public RefreshTokenClaimsUser $user,
        readonly public Carbon $expiredAt,
        public ?string $childID = null,
    ) {
    }
}
