<?php

namespace Tests\Unit\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;

use App\Core\Auth\JWT\Refresh\RefreshTokenExceptionCode;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CacherLaravel_Find_Test extends CacherLaravelBaseTestCase
{
    #[Test]
    public function should_throw_exception_when_is_unused_key_is_not_found()
    {
        // Arrange
        $mockedID = $this->faker->uuid();

        $service = $this->makeService();


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:user:id")
            ->once()
            ->andReturn(false);

        $expectedException =  new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token not found',
            RefreshTokenExceptionCode::NOT_FOUND->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $service->find($mockedID);
    }

    #[Test]
    #[DataProvider('expiredAtValueDataProvider')]
    public function should_return_token_when_found_and_expired_at(mixed $mockedExpiredAtUnix)
    {
        // Arrange
        $mockedID = $this->faker->uuid();
        $expectedToken = new RefreshTokenClaims(
            $mockedID,
            new RefreshTokenClaimsUser($this->faker->uuid(), $this->faker->email),
            Carbon::parse(intval($mockedExpiredAtUnix)),
            $this->faker->uuid,
        );

        $service = $this->makeService();


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:user:id")
            ->once()
            ->andReturn(true);

        $expectedCacheValue = [
            "{$this->getPrefixName()}:{$expectedToken->id}:user:id" => $expectedToken->user->id,
            "{$this->getPrefixName()}:{$expectedToken->id}:user:email" => $expectedToken->user->userEmail,
            "{$this->getPrefixName()}:{$expectedToken->id}:child:id" => $expectedToken->childID,
            "{$this->getPrefixName()}:{$expectedToken->id}:expired-at" => $mockedExpiredAtUnix,
        ];
        Cache::shouldReceive('getMultiple')
            ->with(array_keys($expectedCacheValue))
            ->once()
            ->andReturn($expectedCacheValue);


        // Act
        $result = $service->find($mockedID);


        // Assert
        $this->assertEquals($expectedToken, $result);
    }

    public static function expiredAtValueDataProvider(): array
    {
        $expiredAt = Carbon::parse(self::makeFaker()->dateTime);

        return [
            'unix as integer' => [$expiredAt->unix()],
            'unix as string' => [(string) $expiredAt->unix()],
            'null' => [null],
        ];
    }

    #[Test]
    public function should_return_token_when_found_with_no_parent_id()
    {
        // Arrange
        $mockedID = $this->faker->uuid();
        $expectedToken = new RefreshTokenClaims(
            $mockedID,
            new RefreshTokenClaimsUser($this->faker->uuid(), $this->faker->email),
            Carbon::parse($this->faker->dateTime),
        );

        $service = $this->makeService();


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:user:id")
            ->once()
            ->andReturn(true);

        $expectedCacheValue = [
            "{$this->getPrefixName()}:{$expectedToken->id}:user:id" => $expectedToken->user->id,
            "{$this->getPrefixName()}:{$expectedToken->id}:user:email" => $expectedToken->user->userEmail,
            "{$this->getPrefixName()}:{$expectedToken->id}:child:id" => $expectedToken->childID,
            "{$this->getPrefixName()}:{$expectedToken->id}:expired-at" => $expectedToken->expiredAt->unix(),
        ];
        Cache::shouldReceive('getMultiple')
            ->with(array_keys($expectedCacheValue))
            ->once()
            ->andReturn($expectedCacheValue);


        // Act
        $result = $service->find($mockedID);


        // Assert
        $this->assertEquals($expectedToken, $result);
    }

    #[Test]
    #[DataProvider('notFoundDataProvider')]
    public function should_return_token_when_found_with_some_user_information_not_found(mixed $mockValue)
    {
        // Arrange
        $mockedID = $this->faker->uuid();
        $expectedToken = new RefreshTokenClaims(
            $mockedID,
            new RefreshTokenClaimsUser('', ''),
            Carbon::parse($this->faker->dateTime),
        );

        $service = $this->makeService();


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:user:id")
            ->once()
            ->andReturn(true);

        $expectedCacheValue = [
            "{$this->getPrefixName()}:{$expectedToken->id}:user:id" => $mockValue,
            "{$this->getPrefixName()}:{$expectedToken->id}:user:email" => $mockValue,
            "{$this->getPrefixName()}:{$expectedToken->id}:child:id" => $expectedToken->childID,
            "{$this->getPrefixName()}:{$expectedToken->id}:expired-at" => $expectedToken->expiredAt->unix(),
        ];
        Cache::shouldReceive('getMultiple')
            ->withArgs(function (array $argKeys) use ($expectedCacheValue) {
                $this->assertEqualsCanonicalizing(array_keys($expectedCacheValue), $argKeys);
                return true;
            })->once()
            ->andReturn($expectedCacheValue);


        // Act
        $result = $service->find($mockedID);


        // Assert
        $this->assertEquals($expectedToken, $result);
    }

    public static function notFoundDataProvider(): array
    {
        return [
            'empty string' => [
                '',
            ],
            'null' => [
                null,
            ],
        ];
    }
}
