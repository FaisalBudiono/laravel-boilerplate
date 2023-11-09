<?php

namespace App\Core\Post;

use App\Models\Post\Post;
use App\Port\Core\Post\CreatePostPort;

interface PostCoreContract
{
    public function create(CreatePostPort $request): Post;
}
