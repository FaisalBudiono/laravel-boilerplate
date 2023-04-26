<?php

namespace App\Port\Core\User;

use App\Models\User\User;

interface DeleteUserPort
{
    public function getUserModel(): User;
}
