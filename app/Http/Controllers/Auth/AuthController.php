<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Core\Auth\AuthJWTCoreContract;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Exceptions\Core\Auth\InvalidCredentialException;
use App\Exceptions\Core\Auth\JWT\JWTException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Exceptions\Http\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GetRefreshTokenRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        protected AuthJWTCoreContract $core,
    ) {
    }

    public function login(LoginRequest $request)
    {
        try {
            $tokenPair = $this->core->login($request);

            return response()->json([
                'data' => $tokenPair->toArray(),
            ]);
        } catch (InvalidCredentialException $e) {
            throw new UnauthorizedException($e->exceptionMessage, $e);
        } catch (Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }

    public function logout(LogoutRequest $request)
    {
        try {
            $this->core->logout($request);

            return response()->noContent();
        } catch (JWTException $e) {
            throw new UnauthorizedException($e->exceptionMessage, $e);
        } catch (Throwable $e) {
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }

    public function refresh(GetRefreshTokenRequest $request)
    {
        try {
            $tokenPair = $this->core->refresh($request);

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
