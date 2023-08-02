<?php

namespace App\Http\Controllers\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Exceptions\Http\ConflictException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\GetAllUserRequest;
use App\Http\Requests\User\GetMyInfoRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserCoreContract $core,
    ) {
    }

    public function destroy(DeleteUserRequest $request)
    {
        try {
            $this->core->delete($request);

            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function index(GetAllUserRequest $request)
    {
        try {
            $users = $this->core->getAll($request);

            return UserResource::collection($users);
        } catch (Exception $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function me(GetMyInfoRequest $request)
    {
        try {
            $user = $this->core->get($request);

            return UserResource::make($user)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function show(GetUserRequest $request)
    {
        try {
            $user = $this->core->get($request);

            return UserResource::make($user)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function store(CreateUserRequest $request)
    {
        try {
            $user = $this->core->create($request);

            return UserResource::make($user)
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (UserEmailDuplicatedException $e) {
            throw new ConflictException($e->exceptionMessage);
        } catch (Exception $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function update(UpdateUserRequest $request)
    {
        try {
            $user = $this->core->update($request);

            return UserResource::make($user);
        } catch (UserEmailDuplicatedException $e) {
            throw new ConflictException($e->exceptionMessage);
        } catch (Exception $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }
}
