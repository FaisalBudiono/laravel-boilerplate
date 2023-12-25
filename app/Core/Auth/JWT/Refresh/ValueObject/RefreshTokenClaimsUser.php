<?php

declare(strict_types=1);

namespace App\Core\Auth\JWT\Refresh\ValueObject;

readonly class RefreshTokenClaimsUser
{
    public function __construct(
        public string $id,
        public string $userEmail,
    ) {
    }
}
