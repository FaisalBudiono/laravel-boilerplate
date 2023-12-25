<?php

declare(strict_types=1);

namespace App\Port\Core\Auth;

interface GetRefreshTokenPort
{
    public function getRefreshToken(): string;
}
