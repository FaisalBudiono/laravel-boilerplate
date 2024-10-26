<?php

declare(strict_types=1);

namespace App\Services\Auth\RefreshToken;

use App\Models\User\User;
use App\Services\Auth\RefreshToken\Exceptions\RefreshTokenException;
use App\Services\Auth\ValueObject\RefreshToken;

interface RefreshTokenManager
{
    public function generate(
        User $user,
        string $ipAddress,
        string $userAgent,
        ?string $parentToken,
    ): string;

    /**
     * @throws RefreshTokenException
     */
    public function invalidate(string $token): void;
    public function isValid(string $token): bool;

    /**
     * @throws RefreshTokenException
     */
    public function fetch(string $token): RefreshToken;
}
