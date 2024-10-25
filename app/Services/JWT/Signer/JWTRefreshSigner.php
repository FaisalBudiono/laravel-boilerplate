<?php

declare(strict_types=1);

namespace App\Services\JWT\Signer;

use App\Models\User\User;

interface JWTRefreshSigner
{
    public function sign(User $user, string $sessionPayload): string;
    public function validate(string $token): void;
}
