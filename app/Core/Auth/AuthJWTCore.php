<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Auth\JWT\Mapper\JWTMapperContract;
use App\Core\Auth\JWT\Refresh\RefreshTokenManagerContract;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Auth\JWT\ValueObject\TokenPair;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\InvalidCredentialException;
use App\Exceptions\Models\ModelNotFoundException;
use App\Core\User\Enum\UserExceptionCode;
use App\Models\User\User;
use App\Port\Core\Auth\GetRefreshTokenPort;
use App\Port\Core\Auth\LoginPort;
use App\Port\Core\Auth\LogoutPort;
use Illuminate\Support\Facades\Hash;

class AuthJWTCore implements AuthJWTCoreContract
{
    public function __construct(
        protected JWTMapperContract $jwtMapper,
        protected JWTSigner $jwtSigner,
        protected RefreshTokenManagerContract $refreshTokenManager,
    ) {
    }

    public function login(LoginPort $request): TokenPair
    {
        try {
            $user = User::findByEmailOrFail($request->getUserEmail());

            if (!Hash::check($request->getUserPassword(), $user->password)) {
                $this->throwInvalidCredential();
            }

            $refreshTokenClaims = $this->refreshTokenManager->create($user);

            return new TokenPair(
                $this->jwtSigner->sign($this->jwtMapper->map($user)),
                $refreshTokenClaims->id,
            );
        } catch (ModelNotFoundException $e) {
            $this->throwInvalidCredential($e);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function logout(LogoutPort $request): void
    {
        $this->refreshTokenManager->invalidate($request->getRefreshToken());
    }

    public function refresh(GetRefreshTokenPort $request): TokenPair
    {
        $refreshTokenClaims = $this->refreshTokenManager->refresh($request->getRefreshToken());

        $user = User::findByIDOrFail(intval($refreshTokenClaims->user->id));

        return new TokenPair(
            $this->jwtSigner->sign($this->jwtMapper->map($user)),
            $refreshTokenClaims->id,
        );
    }

    protected function throwInvalidCredential(\Throwable $e = null): never
    {
        throw new InvalidCredentialException(new ExceptionMessageStandard(
            'Credential is invalid',
            UserExceptionCode::INVALID_CREDENTIAL->value,
        ), $e);
    }
}
