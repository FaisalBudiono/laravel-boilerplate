<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;

use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CacherLaravel_Save_Test extends CacherLaravelBaseTestCase
{
    #[Test]
    #[DataProvider('tokenDataProvider')]
    public function should_cache_all_info(RefreshTokenClaims $mockedToken): void
    {
        // Assert
        Cache::shouldReceive('putMany')
            ->with(
                [
                    "{$this->getPrefixName()}:{$mockedToken->id}:user:id" => $mockedToken->user->id,
                    "{$this->getPrefixName()}:{$mockedToken->id}:user:email" => $mockedToken->user->userEmail,
                    "{$this->getPrefixName()}:{$mockedToken->id}:expired-at" => $mockedToken->expiredAt->unix(),
                    "{$this->getPrefixName()}:{$mockedToken->id}:child:id" => $mockedToken->childID,
                    "{$this->getPrefixName()}:{$mockedToken->id}:used-at" => $mockedToken->usedAt?->unix(),
                ],
                $mockedToken->expiredAt,
            )->once();


        // Act
        $this->makeService()->save($mockedToken);
    }

    public static function tokenDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'complete refresh token' => [
                new RefreshTokenClaims(
                    $faker->uuid(),
                    new RefreshTokenClaimsUser($faker->uuid(), $faker->email()),
                    Carbon::parse($faker->dateTime()),
                    $faker->uuid(),
                    Carbon::parse($faker->dateTime()),
                ),
            ],

            'without nullable field' => [
                new RefreshTokenClaims(
                    $faker->uuid(),
                    new RefreshTokenClaimsUser($faker->uuid(), $faker->email()),
                    Carbon::parse($faker->dateTime()),
                    null,
                    null,
                ),
            ],
        ];
    }
}
