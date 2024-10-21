<?php

declare(strict_types=1);

namespace App\Services\JWT\Parser;

use App\Services\JWT\ValueObject\Claims;

interface JWTParser
{
    public function parse(string $token): Claims;
}
