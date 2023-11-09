<?php

namespace App\Core\Post;

use App\Models\Post\Post;
use App\Port\Core\Post\CreatePostPort;
use Illuminate\Support\Facades\DB;

class PostCore implements PostCoreContract
{
    public function create(CreatePostPort $request): Post
    {
        try {
            DB::beginTransaction();

            $post = new Post();
            $post->user_id = $request->getUserActor()->id;
            $post->title = $request->getTitle();
            $post->content = $request->getContent();
            $post->save();

            DB::commit();

            return $post->fresh(Post::eagerLoadAll());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
