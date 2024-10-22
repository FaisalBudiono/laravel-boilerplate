<?php

declare(strict_types=1);

namespace App\Port\Services\Auth;

interface LoginPort
{
    public function getEmail(): string;
    public function getUserPassword(): string;
}
