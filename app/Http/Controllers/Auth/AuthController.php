<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\InvalidCredentialException;
use App\Exceptions\Http\InternalServerError;
use App\Exceptions\Http\NotFound;
use App\Exceptions\Http\Unauthorized;
use App\Exceptions\Resource\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Resources\Auth\Token\TokenResource;
use App\Services\Auth\AuthServiceContract;
use App\Services\Auth\RefreshToken\Exceptions\RefreshTokenException;
use App\Services\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        protected AuthServiceContract $service
    ) {}

    public function destroy(LogoutRequest $req): Response
    {
        try {
            $this->service->logout($req);

            return response()->noContent();
        } catch (RefreshTokenException $e) {
            throw new Unauthorized($e->exceptionMessage, $e);
        } catch (ModelNotFoundException $e) {
            throw new NotFound($e->exceptionMessage, $e);
        } catch (\Throwable $e) {
            throw new InternalServerError(new ExceptionMessageGeneric(), $e);
        }
    }

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

    public function refresh(RefreshTokenRequest $req)
    {
        try {
            $token = $this->service->refreshToken($req);

            return TokenResource::make($token);
        } catch (RefreshTokenException $e) {
            throw new Unauthorized($e->exceptionMessage, $e);
        } catch (ModelNotFoundException $e) {
            throw new NotFound($e->exceptionMessage, $e);
        } catch (\Throwable $e) {
            throw new InternalServerError(new ExceptionMessageGeneric(), $e);
        }
    }
}
