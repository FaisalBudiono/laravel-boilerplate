<?php

declare(strict_types=1);

namespace App\Core\Auth\JWT\ValueObject;

readonly class ClaimsUser
{
    public function __construct(
        public string $id,
        public string $userEmail,
    ) {
    }
}
