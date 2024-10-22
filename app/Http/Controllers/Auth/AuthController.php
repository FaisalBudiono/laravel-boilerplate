<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\InvalidCredentialException;
use App\Exceptions\Http\InternalServerError;
use App\Exceptions\Http\Unauthorized;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\Token\TokenResource;
use App\Services\Auth\AuthServiceContract;
use App\Services\Formatter\ExceptionMessage\ExceptionMessageGeneric;

class AuthController extends Controller
{
    public function __construct(
        protected AuthServiceContract $service
    ) {}

    public function store(LoginRequest $req)
    {
        try {
            $token = $this->service->login($req);

            return TokenResource::make($token);
        } catch (InvalidCredentialException $e) {
            throw new Unauthorized($e->exceptionMessage, $e);
        } catch (\Throwable $e) {
            throw new InternalServerError(new ExceptionMessageGeneric(), $e);
        }
    }
}
