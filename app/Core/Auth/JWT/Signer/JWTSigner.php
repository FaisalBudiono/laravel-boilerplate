<?php

declare(strict_types=1);

namespace App\Core\Auth\JWT\Signer;

use App\Core\Auth\JWT\ValueObject\Claims;

interface JWTSigner
{
    public function sign(Claims $claims): string;
    public function validate(string $token): void;
}
