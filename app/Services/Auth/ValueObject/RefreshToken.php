<?php

declare(strict_types=1);

namespace App\Services\Auth\ValueObject;

readonly class RefreshToken
{
    public function __construct(
        public RefreshTokenUser $user,
        public string $token,
        public ?string $parentToken,
    ) {}
}
