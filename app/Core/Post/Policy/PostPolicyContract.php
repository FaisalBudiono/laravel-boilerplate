<?php

declare(strict_types=1);

namespace App\Core\Post\Policy;

use App\Models\Post\Post;
use App\Models\User\User;

interface PostPolicyContract
{
    public function delete(User $user, Post $post): bool;
    public function seeAll(User $user): bool;
    public function seeUserPost(User $user, User $userFilter): bool;
    public function see(User $user, Post $post): bool;
    public function update(User $user, Post $post): bool;
}
