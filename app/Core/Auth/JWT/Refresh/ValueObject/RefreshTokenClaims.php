<?php

declare(strict_types=1);

namespace App\Core\Auth\JWT\Refresh\ValueObject;

use Carbon\Carbon;

class RefreshTokenClaims
{
    public function __construct(
        public string $id,
        public RefreshTokenClaimsUser $user,
        public ?Carbon $expiredAt,
        public ?string $childID = null,
        public ?Carbon $usedAt = null,
    ) {
    }
}
