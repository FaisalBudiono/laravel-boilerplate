<?php

declare(strict_types=1);

namespace App\Port\Services\Auth;

interface RefreshTokenPort
{
    public function getRefreshToken(): string;
}
