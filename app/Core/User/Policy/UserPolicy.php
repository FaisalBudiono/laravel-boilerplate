<?php

declare(strict_types=1);

namespace App\Core\User\Policy;

use App\Models\Permission\Enum\RoleName;
use App\Models\User\User;

class UserPolicy implements UserPolicyContract
{
    public function delete(User $user, User $userTarget): bool
    {
        return $this->isAdmin($user) || $user->is($userTarget);
    }

    public function seeAll(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function see(User $user, User $userTarget): bool
    {
        return $this->isAdmin($user) || $user->is($userTarget);
    }

    public function update(User $user, User $userTarget): bool
    {
        return $this->isAdmin($user) || $user->is($userTarget);
    }

    protected function isAdmin(User $user): bool
    {
        return $user->roles->contains('name', RoleName::ADMIN);
    }
}
