<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Core\Auth\AuthJWTCoreContract;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\Auth\InvalidCredentialException;
use App\Exceptions\Core\Auth\JWT\JWTException;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Exceptions\Http\ConflictException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Exceptions\Http\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GetRefreshTokenRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        protected AuthJWTCoreContract $authCore,
        protected UserCoreContract $userCore,
    ) {
    }

    public function login(LoginRequest $request): Response
    {
        try {
            $tokenPair = $this->authCore->login($request);

            return response()->json([
                'data' => $tokenPair->toArray(),
            ]);
        } catch (InvalidCredentialException $e) {
            throw new UnauthorizedException($e->exceptionMessage, $e);
        } catch (Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }

    public function logout(LogoutRequest $request): Response
    {
        try {
            $this->authCore->logout($request);

            return response()->noContent();
        } catch (JWTException $e) {
            throw new UnauthorizedException($e->exceptionMessage, $e);
        } catch (Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }

    public function register(RegisterRequest $request): Response
    {
        try {
            $user = $this->userCore->create($request);

            return UserResource::make($user)
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (UserEmailDuplicatedException $e) {
            throw new ConflictException($e->exceptionMessage, $e);
        } catch (Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }

    public function refresh(GetRefreshTokenRequest $request): Response
    {
        try {
            $tokenPair = $this->authCore->refresh($request);

            return response()->json([
                'data' => $tokenPair->toArray()
            ]);
        } catch (JWTException $e) {
            throw new UnauthorizedException($e->exceptionMessage, $e);
        } catch (Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }
}
