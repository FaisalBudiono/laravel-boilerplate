<?php

declare(strict_types=1);

namespace App\Port\Core\User;

use App\Models\User\User;
use App\Port\Core\NeedActorPort;

interface DeleteUserPort extends NeedActorPort
{
    public function getUserModel(): User;
}
