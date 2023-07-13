<?php

namespace App\Core\Auth\JWT\Refresh\Cacher;

use App\Core\Auth\JWT\Refresh\RefreshTokenExceptionCode;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
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
                Cache::forget($this->getIsUnusedKey($destroyableTokenID));
                Cache::forget($this->getExpiredAtKey($destroyableTokenID));
            });
    }

    public function find(string $tokenID): RefreshTokenClaims
    {
        if (!Cache::has($this->getUserIDKey($tokenID))) {
            $this->throwNotFound();
        }

        $values = Cache::getMultiple([
            $this->getUserIDKey($tokenID),
            $this->getUserEmailKey($tokenID),
            $this->getChildIDKey($tokenID),
            $this->getExpiredAtKey($tokenID),
        ]);

        return new RefreshTokenClaims(
            $tokenID,
            new ClaimsUser(
                $values[$this->getUserIDKey($tokenID)] ?? '',
                $values[$this->getUserEmailKey($tokenID)] ?? '',
            ),
            $this->formatExpiredAt($values[$this->getExpiredAtKey($tokenID)]),
            $values[$this->getChildIDKey($tokenID)],
        );
    }

    public function invalidate(string $tokenID): void
    {
        if (
            !Cache::has($this->getIsUnusedKey($tokenID))
            || !Cache::has($this->getExpiredAtKey($tokenID))
        ) {
            $this->throwNotFound();
        }

        Cache::put(
            $this->getIsUnusedKey($tokenID),
            1,
            $this->formatExpiredAt(Cache::get($this->getExpiredAtKey($tokenID))),
        );
    }

    public function isUnused(string $tokenID): bool
    {
        if (!Cache::has($this->getIsUnusedKey($tokenID))) {
            $this->throwNotFound();
        }

        $isUnusedValue = Cache::get($this->getIsUnusedKey($tokenID));
        $isUnusedTotal = is_null($isUnusedValue) ? null : intval($isUnusedValue);

        return $isUnusedTotal === 0;
    }

    public function save(RefreshTokenClaims $refreshTokenClaims): void
    {
        Cache::putMany([
            $this->getUserIDKey($refreshTokenClaims->id) => $refreshTokenClaims->user->id,
            $this->getUserEmailKey($refreshTokenClaims->id) => $refreshTokenClaims->user->userEmail,
            $this->getChildIDKey($refreshTokenClaims->id) => $refreshTokenClaims->childID,
            $this->getIsUnusedKey($refreshTokenClaims->id) => 0,
            $this->getExpiredAtKey($refreshTokenClaims->id) => $refreshTokenClaims->expiredAt->unix(),
        ], $refreshTokenClaims->expiredAt);
    }

    public function setChildID(string $parentID, string $childID): void
    {
        if (
            !Cache::has($this->getChildIDKey($parentID))
            || !Cache::has($this->getExpiredAtKey($parentID))
        ) {
            $this->throwNotFound();
        }

        Cache::put(
            $this->getChildIDKey($parentID),
            $childID,
            $this->formatExpiredAt(Cache::get($this->getExpiredAtKey($parentID))),
        );
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

    protected function formatExpiredAt(mixed $unix): Carbon
    {
        return Carbon::parse(intval($unix));
    }

    protected function getExpiredAtKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:expired-at";
    }

    protected function getChildIDKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:child:id";
    }

    protected function getIsUnusedKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:is-unused";
    }

    protected function getPrefixName(): string
    {
        return config('jwt.refresh.prefix');
    }

    protected function getUserIDKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:user:id";
    }

    protected function getUserEmailKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:user:email";
    }

    protected function throwNotFound(): never
    {
        throw  new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token not found',
            RefreshTokenExceptionCode::NOT_FOUND->value,
        ));
    }
}
