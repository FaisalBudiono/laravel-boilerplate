<?php

declare(strict_types=1);

namespace App\Services\JWT\Parser;

use App\Services\JWT\ValueObject\RefreshClaims;

interface JWTRefreshParser
{
    public function parse(string $token): RefreshClaims;
}
