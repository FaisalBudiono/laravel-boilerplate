<?php

namespace App\Port\Core\Auth;

interface LoginPort
{
    public function getUserEmail(): string;
    public function getUserPassword(): string;
}
