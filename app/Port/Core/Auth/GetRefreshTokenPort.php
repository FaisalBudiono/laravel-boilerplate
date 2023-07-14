<?php

namespace App\Port\Core\Auth;

interface GetRefreshTokenPort
{
    public function getRefreshToken(): string;
}
