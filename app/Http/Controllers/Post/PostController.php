<?php

namespace App\Http\Controllers\Post;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Post\PostCoreContract;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\GetSinglePostRequest;
use App\Http\Resources\Post\PostResource;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function __construct(
        protected PostCoreContract $core,
    ) {
    }

    public function show(GetSinglePostRequest $request): Response
    {
        try {
            $post = $this->core->get($request);

            return PostResource::make($post)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
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
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }
}
