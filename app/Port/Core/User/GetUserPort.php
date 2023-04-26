<?php

namespace App\Port\Core\User;

use App\Models\User\User;

interface GetUserPort
{
    public function getUserModel(): User;
}
