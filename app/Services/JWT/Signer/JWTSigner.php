<?php

declare(strict_types=1);

namespace App\Services\JWT\Signer;

use App\Services\JWT\ValueObject\Claims;

interface JWTSigner
{
    public function sign(Claims $claims): string;
    public function validate(string $token): void;
}
