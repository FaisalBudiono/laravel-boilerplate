<?php

namespace App\Port\Core\Auth;

interface LogoutPort
{
    public function getRefreshToken(): string;
}
