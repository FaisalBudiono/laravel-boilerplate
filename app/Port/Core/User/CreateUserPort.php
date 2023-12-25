<?php

declare(strict_types=1);

namespace App\Port\Core\User;

interface CreateUserPort
{
    public function getName(): string;
    public function getEmail(): string;
    public function getUserPassword(): string;
}
