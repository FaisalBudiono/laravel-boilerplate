<?php

declare(strict_types=1);

namespace App\Core\Post;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Post\Enum\PostExceptionCode;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
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
            $post->content = $request->getPostContent();
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

            $post = $request->getPost();
            if ($request->getUserActor()->cannot('delete', $post)) {
                throw new InsufficientPermissionException(new ExceptionMessageStandard(
                    'Insufficient permission to delete post',
                    PostExceptionCode::PERMISSION_INSUFFICIENT->value,
                ));
            }

            $post->delete();

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
        $userActor = $request->getUserActor();
        $userFilter = $request->getUserFilter();

        $wantToSeeAll = is_null($userFilter);
        if ($wantToSeeAll && $userActor->cannot('seeAll', Post::class)) {
            $this->throwInsufficientPermission();
        }

        $perPage = $request->getPerPage() ?? 15;
        $wantToFilteredByUser = !is_null($userFilter);

        return Post::with(Post::eagerLoadAll())
            ->when($wantToFilteredByUser, function (Builder $q) use ($userFilter, $userActor) {
                if ($userActor->cannot('see-user-post', [Post::class, $userFilter])) {
                    $this->throwInsufficientPermission();
                }

                $q->where('user_id', $userFilter->id);
            })->paginate($perPage, ['*'], 'page', $request->getPage());
    }

    public function get(GetSinglePostPort $request): Post
    {
        $post = $request->getPost();

        if ($request->getUserActor()->cannot('see', $post)) {
            throw new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to see post',
                PostExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
        }

        return $post->fresh(Post::eagerLoadAll());
    }

    public function update(UpdatePostPort $request): Post
    {
        try {
            DB::beginTransaction();

            $post = $request->getPost();
            $post->user_id = $request->getUserActor()->id;
            $post->title = $request->getTitle();
            $post->content = $request->getPostContent();
            $post->save();

            DB::commit();

            return $post->fresh(Post::eagerLoadAll());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function throwInsufficientPermission(): never
    {
        throw new InsufficientPermissionException(new ExceptionMessageStandard(
            'Insufficient permission to fetch posts',
            PostExceptionCode::PERMISSION_INSUFFICIENT->value,
        ));
    }
}
