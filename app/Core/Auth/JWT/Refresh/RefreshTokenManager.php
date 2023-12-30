<?php

declare(strict_types=1);

namespace App\Core\Auth\JWT\Refresh;

use App\Core\Auth\JWT\Refresh\Cacher\Cacher;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapperContract;
use App\Models\User\User;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use Carbon\Carbon;

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
        $refreshToken = $this->cacher->find($tokenID);

        $this->validateTokenExpiry($refreshToken->expiredAt);

        $isUsedBefore = !is_null($refreshToken->usedAt);
        if ($isUsedBefore) {
            throw new InvalidTokenException(new ExceptionMessageStandard(
                'Refresh token is already used before.',
                RefreshTokenExceptionCode::TOKEN_IS_USED->value,
            ));
        }

        $refreshToken->usedAt = now();
        $this->cacher->save($refreshToken);
    }

    public function refresh(string $tokenID): RefreshTokenClaims
    {
        $oldRefreshToken = $this->cacher->find($tokenID);

        $isAlreadyUsed = !is_null($oldRefreshToken->usedAt);
        if ($isAlreadyUsed) {
            if (!$this->isInGracePeriod($oldRefreshToken->usedAt)) {
                throw new InvalidTokenException(new ExceptionMessageStandard(
                    'Refresh token is already used before.',
                    RefreshTokenExceptionCode::TOKEN_IS_USED->value,
                ));
            }

            if (is_null($oldRefreshToken->childID)) {
                throw new InvalidTokenException(new ExceptionMessageStandard(
                    'Token not found.',
                    RefreshTokenExceptionCode::NOT_FOUND->value,
                ));
            }

            return $this->cacher->find($oldRefreshToken->childID);
        }

        $this->validateTokenExpiry($oldRefreshToken->expiredAt);

        $newRefreshedToken = $this->userTokenMapper->map(
            User::findByIDOrFail(intval($oldRefreshToken->user->id))
        );

        $this->cacher->save($newRefreshedToken);

        $oldRefreshToken->usedAt = now();
        $oldRefreshToken->childID = $newRefreshedToken->id;
        $this->cacher->save($oldRefreshToken);

        return $newRefreshedToken;
    }

    protected function getRefreshGracePeriodInSecond(): int
    {
        return intval(config('jwt.refresh.grace-period'));
    }

    protected function isInGracePeriod(Carbon $usedAt): bool
    {
        return $usedAt->clone()
            ->addSeconds($this->getRefreshGracePeriodInSecond())
            ->isAfter(now());
    }

    protected function validateTokenExpiry(?Carbon $expiredAt): void
    {
        $isExpired = is_null($expiredAt) || $expiredAt->isBefore(now());

        if ($isExpired) {
            throw new InvalidTokenException(new ExceptionMessageStandard(
                'Refresh token is expired',
                RefreshTokenExceptionCode::EXPIRED->value
            ));
        }
    }
}
