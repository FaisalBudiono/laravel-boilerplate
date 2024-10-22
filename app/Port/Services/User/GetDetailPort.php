<?php

declare(strict_types=1);

namespace App\Port\Services\User;

use App\Models\User\User;

interface GetDetailPort
{
    public function getLoggedInUser(): User;
}
