<?php

declare(strict_types=1);

namespace App\Port\Services\Auth;

interface LogoutPort
{
    public function getRefreshToken(): string;
}
