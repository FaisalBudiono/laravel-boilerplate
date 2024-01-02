<?php

declare(strict_types=1);

namespace App\Port\Core\User;

use App\Models\User\User;
use App\Port\Core\NeedActorPort;

interface UpdateUserPort extends NeedActorPort
{
    public function getEmail(): string;
    public function getName(): string;
    public function getUserModel(): User;
    public function getUserPassword(): string;
}
