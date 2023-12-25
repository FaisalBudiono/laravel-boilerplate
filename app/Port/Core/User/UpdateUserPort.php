<?php

declare(strict_types=1);

namespace App\Port\Core\User;

use App\Models\User\User;

interface UpdateUserPort
{
    public function getEmail(): string;
    public function getName(): string;
    public function getUserModel(): User;
    public function getUserPassword(): string;
}
