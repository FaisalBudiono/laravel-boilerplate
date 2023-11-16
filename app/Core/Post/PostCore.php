<?php

namespace App\Core\Post;

use App\Models\Permission\Enum\RoleName;
use App\Models\Post\Post;
use App\Port\Core\Post\CreatePostPort;
use App\Port\Core\Post\DeletePostPort;
use App\Port\Core\Post\GetAllPostPort;
use App\Port\Core\Post\GetSinglePostPort;
use App\Port\Core\Post\UpdatePostPort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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

    public function delete(DeletePostPort $request): void
    {
        try {
            DB::beginTransaction();

            $request->getPost()->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function getAll(GetAllPostPort $request): LengthAwarePaginator
    {
        $perPage = $request->getPerPage() ?? 15;

        $userActor = $request->getUserActor();
        $userFilter = $request->getUserFilter();

        $isAdmin = $userActor->roles->contains('name', RoleName::ADMIN);
        $shouldFilterByUser = !$isAdmin || !is_null($userFilter);


        return Post::with(Post::eagerLoadAll())
            ->when($shouldFilterByUser, function (Builder $q) use ($userFilter, $userActor, $isAdmin) {
                if (!$isAdmin) {
                    $q->where('user_id', $userActor->id);
                    return;
                }
                $q->where('user_id', $userFilter->id);
            })->paginate($perPage, ['*'], 'page', $request->getPage());
    }

    public function get(GetSinglePostPort $request): Post
    {
        return $request->getPost()->fresh(Post::eagerLoadAll());
    }

    public function update(UpdatePostPort $request): Post
    {
        try {
            DB::beginTransaction();

            $post = $request->getPost();
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
