<?php

declare(strict_types=1);

namespace App\Http\Controllers\Post;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Post\PostCoreContract;
use App\Exceptions\Core\Auth\Permission\PermissionException;
use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\DeletePostRequest;
use App\Http\Requests\Post\GetAllPostRequest;
use App\Http\Requests\Post\GetSinglePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function __construct(
        protected PostCoreContract $core,
    ) {
    }

    public function destroy(DeletePostRequest $request): Response
    {
        try {
            $this->core->delete($request);

            return response()->noContent();
        } catch (PermissionException $e) {
            throw new ForbiddenException($e->exceptionMessage, $e);
        } catch (\Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }

    public function index(GetAllPostRequest $request): Response
    {
        try {
            $posts = $this->core->getAll($request);

            return PostResource::collection($posts)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (PermissionException $e) {
            throw new ForbiddenException($e->exceptionMessage, $e);
        } catch (\Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }

    public function show(GetSinglePostRequest $request): Response
    {
        try {
            $post = $this->core->get($request);

            return PostResource::make($post)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (PermissionException $e) {
            throw new ForbiddenException($e->exceptionMessage, $e);
        } catch (\Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }

    public function store(CreatePostRequest $request): Response
    {
        try {
            $post = $this->core->create($request);

            return PostResource::make($post)
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }

    public function update(UpdatePostRequest $request): Response
    {
        try {
            $post = $this->core->update($request);

            return PostResource::make($post)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
            return response()->json();
        } catch (PermissionException $e) {
            throw new ForbiddenException($e->exceptionMessage, $e);
        } catch (\Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }
}
