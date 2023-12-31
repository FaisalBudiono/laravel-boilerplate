<?php

declare(strict_types=1);

namespace App\Core\Post\Policy;

use App\Models\Permission\Enum\RoleName;
use App\Models\Post\Post;
use App\Models\User\User;

class PostPolicy implements PostPolicyContract
{
    public function delete(User $user, Post $post): bool
    {
        return $this->isOwner($user, $post) || $this->isAdmin($user);
    }

    public function seeAll(User $user): bool
    {
        return $user->roles->contains('name', RoleName::ADMIN);
    }

    public function see(User $user, Post $post): bool
    {
        return $this->isOwner($user, $post) || $this->isAdmin($user);
    }

    public function update(User $user, Post $post): bool
    {
        return $this->isOwner($user, $post) || $this->isAdmin($user);
    }

    protected function isAdmin(User $user): bool
    {
        return $user->roles->contains('name', RoleName::ADMIN);
    }

    protected function isOwner(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }
}
