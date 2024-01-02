<?php

declare(strict_types=1);

namespace App\Port\Core;

use App\Models\User\User;

interface NeedActorPort
{
    public function getUserActor(): User;
}
