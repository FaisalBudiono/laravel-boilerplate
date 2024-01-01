<?php

declare(strict_types=1);

namespace App\Core\User\Policy;

use App\Models\User\User;

interface UserPolicyContract
{
    public function delete(User $user, User $userTarget): bool;
    public function seeAll(User $user): bool;
    public function see(User $user, User $userTarget): bool;
    public function update(User $user, User $userTarget): bool;
}
