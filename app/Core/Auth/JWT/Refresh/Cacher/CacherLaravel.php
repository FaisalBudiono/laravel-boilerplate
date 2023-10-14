<?php

namespace App\Core\Auth\JWT\Refresh\Cacher;

use App\Core\Auth\JWT\Refresh\RefreshTokenExceptionCode;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CacherLaravel implements Cacher
{
    public function deleteAllGenerations(string $tokenID): void
    {
        $this->fetchDestroyableTokenIDs($tokenID)
            ->each(function (string $destroyableTokenID) {
                Cache::forget($this->getUserIDKey($destroyableTokenID));
                Cache::forget($this->getUserEmailKey($destroyableTokenID));
                Cache::forget($this->getChildIDKey($destroyableTokenID));
                Cache::forget($this->getUsedAtKey($destroyableTokenID));
                Cache::forget($this->getExpiredAtKey($destroyableTokenID));
            });
    }

    public function find(string $tokenID): RefreshTokenClaims
    {
        if (!Cache::has($this->getUserIDKey($tokenID))) {
            throw new InvalidTokenException(new ExceptionMessageStandard(
                'Refresh token not found',
                RefreshTokenExceptionCode::NOT_FOUND->value,
            ));
        }

        $values = Cache::getMultiple([
            $this->getUserIDKey($tokenID),
            $this->getUserEmailKey($tokenID),
            $this->getExpiredAtKey($tokenID),
            $this->getChildIDKey($tokenID),
            $this->getUsedAtKey($tokenID),
        ]);

        return new RefreshTokenClaims(
            $tokenID,
            new RefreshTokenClaimsUser(
                $values[$this->getUserIDKey($tokenID)] ?? '',
                $values[$this->getUserEmailKey($tokenID)] ?? '',
            ),
            $this->formatDateFromUnix($values[$this->getExpiredAtKey($tokenID)]),
            $values[$this->getChildIDKey($tokenID)],
            $this->formatDateFromUnix($values[$this->getUsedAtKey($tokenID)]),
        );
    }

    public function save(RefreshTokenClaims $refreshTokenClaims): void
    {
        Cache::putMany([
            $this->getUserIDKey($refreshTokenClaims->id) => $refreshTokenClaims->user->id,
            $this->getUserEmailKey($refreshTokenClaims->id) => $refreshTokenClaims->user->userEmail,
            $this->getChildIDKey($refreshTokenClaims->id) => $refreshTokenClaims->childID,
            $this->getExpiredAtKey($refreshTokenClaims->id) => $refreshTokenClaims->expiredAt->unix(),
            $this->getUsedAtKey($refreshTokenClaims->id) => $refreshTokenClaims->usedAt?->unix(),
        ], $refreshTokenClaims->expiredAt);
    }

    protected function fetchDestroyableTokenIDs(string $tokenID): Collection
    {
        $tokenGenerationIDs = collect([$tokenID]);

        $childID = $tokenID;
        while (!is_null($childID)) {
            $childID = Cache::get($this->getChildIDKey($childID));

            $tokenGenerationIDs->push($childID);
        }

        return $tokenGenerationIDs->filter();
    }

    protected function formatDateFromUnix(mixed $unix): ?Carbon
    {
        return is_null($unix) ? null : Carbon::parse(intval($unix));
    }

    protected function getExpiredAtKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:expired-at";
    }

    protected function getChildIDKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:child:id";
    }

    protected function getPrefixName(): string
    {
        return config('jwt.refresh.prefix');
    }

    protected function getUsedAtKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:used-at";
    }

    protected function getUserIDKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:user:id";
    }

    protected function getUserEmailKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:user:email";
    }
}
