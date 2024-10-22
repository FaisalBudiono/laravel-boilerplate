<?php

declare(strict_types=1);

namespace App\Services\JWT\Signer;

use App\Models\User\User;

interface JWTSigner
{
    public function sign(User $user): string;
    public function validate(string $token): void;
}
