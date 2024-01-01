<?php

declare(strict_types=1);

namespace App\Port\Core\User;

use App\Models\User\User;

interface GetUserPort
{
    public function getUserActor(): User;

    public function getUserModel(): User;
}
