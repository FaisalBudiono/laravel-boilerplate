<?php

namespace App\Port\Core\Post;

use App\Models\Post\Post;
use App\Models\User\User;

interface UpdatePostPort
{
    public function getUserActor(): User;

    public function getPost(): Post;
    public function getTitle(): string;
    public function getPostContent(): ?string;
}
