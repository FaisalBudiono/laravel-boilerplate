<?php

namespace App\Http\Controllers\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Exceptions\Http\ConflictException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Resources\User\UserResource;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserCoreContract $core
    ) {
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
        } catch (\Throwable $th) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }
}
