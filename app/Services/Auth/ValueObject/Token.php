<?php

declare(strict_types=1);

namespace App\Services\Auth\ValueObject;

readonly class Token
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
    ) {}
}
