<?php

namespace App\Port\Core\Post;

use App\Models\User\User;

interface CreatePostPort
{
    public function getUserActor(): User;

    public function getTitle(): string;
    public function getContent(): ?string;
}
