<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Exceptions\Auth\InvalidCredentialException;
use App\Exceptions\Http\InternalServerError;
use App\Exceptions\Http\Unauthorized;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\GetMeRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Services\User\UserServiceContract;

class UserController extends Controller
{
    public function __construct(
        protected UserServiceContract $service
    ) {}

    public function me(GetMeRequest $req)
    {
        try {
            $user = $this->service->getDetail($req);

            return UserResource::make($user);
        } catch (InvalidCredentialException $e) {
            throw new Unauthorized($e->exceptionMessage, $e);
        } catch (\Throwable $e) {
            throw new InternalServerError(new ExceptionMessageGeneric(), $e);
        }
    }
}
