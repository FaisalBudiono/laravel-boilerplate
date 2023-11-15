<?php

namespace App\Policies\Post;

use App\Models\Permission\Enum\RoleName;
use App\Models\User\User;

class PostPolicy
{
    public function seeAll(User $user): bool
    {
        return $user->roles->contains('name', RoleName::ADMIN);
    }
}
