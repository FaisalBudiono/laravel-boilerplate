<?php

declare(strict_types=1);

namespace App\Services\Auth\ValueObject;

readonly class RefreshTokenUser
{
    public function __construct(
        public string $id,
        public string $email,
    ) {}
}
