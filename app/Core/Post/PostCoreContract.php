<?php

namespace App\Core\Post;

use App\Models\Post\Post;
use App\Port\Core\Post\CreatePostPort;
use App\Port\Core\Post\DeletePostPort;
use App\Port\Core\Post\GetAllPostPort;
use App\Port\Core\Post\GetSinglePostPort;
use App\Port\Core\Post\UpdatePostPort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PostCoreContract
{
    public function create(CreatePostPort $request): Post;
    public function delete(DeletePostPort $request): void;
    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function getAll(GetAllPostPort $request): LengthAwarePaginator;
    public function get(GetSinglePostPort $request): Post;
    public function update(UpdatePostPort $request): Post;
}
