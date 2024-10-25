<?php

declare(strict_types=1);

namespace App\Services\JWT\ValueObject;

readonly class RefreshClaimsUser
{
    public function __construct(
        public string $id,
        public string $email,
    ) {}
}
