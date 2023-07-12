<?php

namespace App\Core\Auth\JWT\Refresh;

use App\Core\Auth\JWT\Refresh\Cacher\Cacher;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapperContract;
use App\Models\User\User;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;

class RefreshTokenManager implements RefreshTokenManagerContract
{
    public function __construct(
        protected UserTokenMapperContract $userTokenMapper,
        protected Cacher $cacher,
    ) {
    }

    public function create(User $user): RefreshTokenClaims
    {
        $token = $this->userTokenMapper->map($user);

        $this->cacher->save($token);

        return $token;
    }

    public function invalidate(string $tokenID): void
    {
        $this->cacher->invalidate($tokenID);
    }

    public function refresh(string $tokenID): RefreshTokenClaims
    {
        if (!$this->cacher->isUnused($tokenID)) {
            $this->cacher->deleteAllGenerations($tokenID);

            throw new InvalidTokenException(new ExceptionMessageStandard(
                'Refresh token is already used before.',
                RefreshTokenExceptionCode::TOKEN_IS_USED->value,
            ));
        }

        $token = $this->cacher->find($tokenID);

        if ($token->expiredAt->isBefore(now())) {
            throw new InvalidTokenException(new ExceptionMessageStandard(
                'Refresh token is expired',
                RefreshTokenExceptionCode::EXPIRED->value
            ));
        }

        $refreshedToken = $this->userTokenMapper->map(
            User::findByIdOrFail($token->user->id)
        );

        $this->cacher->save($refreshedToken);
        $this->cacher->invalidate($tokenID);
        $this->cacher->setChildID($tokenID, $refreshedToken->id);

        return $refreshedToken;
    }
}
