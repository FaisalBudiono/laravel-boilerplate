<?php

namespace App\Core\Post;

use App\Models\Post\Post;
use App\Port\Core\Post\CreatePostPort;
use App\Port\Core\Post\GetAllPostPort;
use App\Port\Core\Post\UpdatePostPort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PostCoreContract
{
    public function create(CreatePostPort $request): Post;
    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function getAll(GetAllPostPort $request): LengthAwarePaginator;
    public function update(UpdatePostPort $request): Post;
}
