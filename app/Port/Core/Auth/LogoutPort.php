<?php

declare(strict_types=1);

namespace App\Port\Core\Auth;

interface LogoutPort
{
    public function getRefreshToken(): string;
}
