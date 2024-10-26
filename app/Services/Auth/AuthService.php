<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Exceptions\Auth\InvalidCredentialException;
use App\Exceptions\Resource\ModelNotFoundException;
use App\Models\User\User;
use App\Port\Services\Auth\LoginPort;
use App\Port\Services\Auth\LogoutPort;
use App\Port\Services\Auth\RefreshTokenPort;
use App\Services\Auth\Exceptions\AuthErrorCode;
use App\Services\Auth\RefreshToken\RefreshTokenManager;
use App\Services\Auth\ValueObject\Token;
use App\Services\Formatter\ExceptionErrorCode;
use App\Services\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Services\JWT\Signer\JWTSigner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceContract
{
    public function __construct(
        protected JWTSigner $signer,
        protected RefreshTokenManager $refreshTokenManager,
    ) {}

    public function login(LoginPort $port): Token
    {
        try {
            DB::beginTransaction();

            $u = User::query()->where('email', $port->getEmail())->first();

            if (is_null($u)) {
                throw $this->invalidCredException();
            }

            if (!Hash::check($port->getUserPassword(), $u->password)) {
                throw $this->invalidCredException();
            }

            $at = $this->signer->sign($u);
            $rf = $this->refreshTokenManager->generate(
                $u,
                $port->getIPAdress(),
                $port->getUserAgent(),
                null,
            );

            DB::commit();

            return new Token($at, $rf);
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function logout(LogoutPort $port): void
    {
        try {
            DB::beginTransaction();

            $this->refreshTokenManager->invalidate($port->getRefreshToken());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function refreshToken(RefreshTokenPort $port): Token
    {
        $refreshToken = $port->getRefreshToken();
        $refreshTokenVO = $this->refreshTokenManager->fetch($refreshToken);

        $u = User::query()->where('id', $refreshTokenVO->user->id)->first();
        if (is_null($u)) {
            throw new ModelNotFoundException(new ExceptionMessageStandard(
                'User not found',
                ExceptionErrorCode::RESOURCE_NOT_FOUND->value,
            ));
        }

        return new Token($this->signer->sign($u), $refreshToken);
    }

    protected function invalidCredException(): InvalidCredentialException
    {
        return new InvalidCredentialException(
            new ExceptionMessageStandard(
                'Credential is invalid',
                AuthErrorCode::INVALID_CREDENTIAL->value,
            ),
        );
    }
}
