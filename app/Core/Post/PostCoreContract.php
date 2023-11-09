<?php

namespace App\Core\Post;

use App\Models\Post\Post;
use App\Port\Core\Post\CreatePostPort;
use App\Port\Core\Post\UpdatePostPort;

interface PostCoreContract
{
    public function create(CreatePostPort $request): Post;
    public function update(UpdatePostPort $request): Post;
}
