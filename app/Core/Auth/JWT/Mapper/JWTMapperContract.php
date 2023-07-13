<?php

namespace App\Core\Auth\JWT\Mapper;

use App\Core\Auth\JWT\ValueObject\Claims;
use App\Models\User\User;

interface JWTMapperContract
{
    public function map(User $user): Claims;
}
