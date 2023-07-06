<?php

namespace App\Core\Auth\JWT\Parser;

use App\Core\Auth\JWT\ValueObject\Claims;

interface JWTParser
{
    public function issue(string $token): Claims;
    public function parse(Claims $claims): string;
}
