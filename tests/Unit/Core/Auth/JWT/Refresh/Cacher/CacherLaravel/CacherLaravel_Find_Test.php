<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;

use App\Core\Auth\JWT\Refresh\RefreshTokenExceptionCode;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CacherLaravel_Find_Test extends CacherLaravelBaseTestCase
{
    #[Test]
    public function should_throw_exception_when_is_unused_key_is_not_found(): void
    {
        // Arrange
        $mockedID = $this->faker->uuid();


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:user:id")
            ->once()
            ->andReturn(false);


        try {
            // Act
            $this->makeService()->find($mockedID);
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            $expectedException =  new InvalidTokenException(new ExceptionMessageStandard(
                'Refresh token not found',
                RefreshTokenExceptionCode::NOT_FOUND->value,
            ));
            $this->assertEquals($expectedException, $e);
        }
    }

    #[Test]
    #[DataProvider('notFoundDataProvider')]
    public function should_return_token_when_found_with_some_user_information_not_found(
        mixed $mockValue,
    ): void {
        // Arrange
        $mockedID = $this->faker->uuid();
        $expectedToken = new RefreshTokenClaims(
            $mockedID,
            new RefreshTokenClaimsUser('', ''),
            Carbon::parse($this->faker->dateTime),
            $this->faker->uuid(),
            Carbon::parse($this->faker->dateTime),
        );


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:user:id")
            ->once()
            ->andReturn(true);

        $expectedCacheValue = [
            "{$this->getPrefixName()}:{$expectedToken->id}:user:id" => $mockValue,
            "{$this->getPrefixName()}:{$expectedToken->id}:user:email" => $mockValue,
            "{$this->getPrefixName()}:{$expectedToken->id}:expired-at" => $expectedToken->expiredAt->unix(),
            "{$this->getPrefixName()}:{$expectedToken->id}:child:id" => $expectedToken->childID,
            "{$this->getPrefixName()}:{$expectedToken->id}:used-at" => $expectedToken->usedAt->unix(),
        ];
        Cache::shouldReceive('getMultiple')
            ->withArgs(function (array $argKeys) use ($expectedCacheValue) {
                $this->assertEqualsCanonicalizing(array_keys($expectedCacheValue), $argKeys);
                return true;
            })->once()
            ->andReturn($expectedCacheValue);


        // Act
        $result = $this->makeService()->find($mockedID);


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

    #[Test]
    #[DataProvider('partialDataProvider')]
    public function should_return_token(RefreshTokenClaims $expectedToken): void
    {
        // Arrange
        $mockedID = $expectedToken->id;


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:user:id")
            ->once()
            ->andReturn(true);

        $expectedCacheValue = [
            "{$this->getPrefixName()}:{$expectedToken->id}:user:id" => $expectedToken->user->id,
            "{$this->getPrefixName()}:{$expectedToken->id}:user:email" => $expectedToken->user->userEmail,
            "{$this->getPrefixName()}:{$expectedToken->id}:expired-at" => $expectedToken->expiredAt->unix(),
            "{$this->getPrefixName()}:{$expectedToken->id}:child:id" => $expectedToken->childID,
            "{$this->getPrefixName()}:{$expectedToken->id}:used-at" => $expectedToken->usedAt?->unix(),
        ];
        Cache::shouldReceive('getMultiple')
            ->with(array_keys($expectedCacheValue))
            ->once()
            ->andReturn($expectedCacheValue);


        // Act
        $result = $this->makeService()->find($mockedID);


        // Assert
        $this->assertEquals($expectedToken, $result);
    }

    public static function partialDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'complete data' => [
                new RefreshTokenClaims(
                    $faker->uuid(),
                    new RefreshTokenClaimsUser($faker->uuid(), $faker->email),
                    Carbon::parse($faker->dateTime),
                    $faker->uuid(),
                    Carbon::parse($faker->dateTime),
                ),
            ],
            'without optional param' => [
                new RefreshTokenClaims(
                    $faker->uuid(),
                    new RefreshTokenClaimsUser($faker->uuid(), $faker->email),
                    Carbon::parse($faker->dateTime),
                ),
            ],
        ];
    }

    #[Test]
    public function should_return_token_even_when_all_cache_value_is_returning_null(): void
    {
        // Arrange
        $mockedID = $this->faker->uuid();


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:user:id")
            ->once()
            ->andReturn(true);

        $expectedCacheValue = [
            "{$this->getPrefixName()}:{$mockedID}:user:id" => null,
            "{$this->getPrefixName()}:{$mockedID}:user:email" => null,
            "{$this->getPrefixName()}:{$mockedID}:expired-at" => null,
            "{$this->getPrefixName()}:{$mockedID}:child:id" => null,
            "{$this->getPrefixName()}:{$mockedID}:used-at" => null,
        ];
        Cache::shouldReceive('getMultiple')
            ->with(array_keys($expectedCacheValue))
            ->once()
            ->andReturn($expectedCacheValue);


        // Act
        $result = $this->makeService()->find($mockedID);


        // Assert
        $expectedToken = new RefreshTokenClaims(
            $mockedID,
            new RefreshTokenClaimsUser('', ''),
            null,
            null,
            null,
        );
        $this->assertEquals($expectedToken, $result);
    }
}
