<?php

namespace App\Core\Auth\JWT\Parser;

use App\Core\Auth\JWT\ValueObject\Claims;

interface JWTParser
{
    public function parse(string $token): Claims;
}
