<?php

declare(strict_types=1);

namespace App\Core\User\Policy;

use App\Models\User\User;

class UserPolicy implements UserPolicyContract
{
    public function delete(User $user, User $userTarget): bool
    {
        return true;
    }

    public function seeAll(User $user): bool
    {
        return true;
    }

    public function see(User $user, User $userTarget): bool
    {
        return true;
    }

    public function update(User $user, User $userTarget): bool
    {
        return true;
    }
}
