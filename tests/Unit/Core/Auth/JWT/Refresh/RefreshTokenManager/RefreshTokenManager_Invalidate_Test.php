<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\Refresh\RefreshTokenManager;

use App\Core\Auth\JWT\Refresh\Cacher\Cacher;
use App\Core\Auth\JWT\Refresh\RefreshTokenExceptionCode;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use Carbon\Carbon;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class RefreshTokenManager_Invalidate_Test extends RefreshTokenManagerBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $now = now();
        Carbon::setTestNow($now);
    }

    #[Test]
    public function should_throw_invalid_token_exception_when_expired_at_is_null(): void
    {
        // Arrange
        $mockedID = $this->faker->uuid();
        $mockedRefreshToken = new RefreshTokenClaims(
            $mockedID,
            new RefreshTokenClaimsUser($this->faker->uuid(), $this->faker->email()),
            null
        );

        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use ($mockedRefreshToken) {
                $mock->shouldReceive('find')
                    ->once()
                    ->with($mockedRefreshToken->id)
                    ->andReturn($mockedRefreshToken);
            }
        );
        assert($mockCacher instanceof Cacher);


        // Assert
        $mockException = new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token is expired',
            RefreshTokenExceptionCode::EXPIRED->value
        ));
        $this->expectExceptionObject($mockException);


        // Act
        $this->makeService(null, $mockCacher)->invalidate($mockedID);
    }

    #[Test]
    public function should_throw_invalid_token_exception_when_refresh_token_already_used(): void
    {
        // Arrange
        $mockedID = $this->faker->uuid();
        $mockedRefreshToken = new RefreshTokenClaims(
            $mockedID,
            new RefreshTokenClaimsUser($this->faker->uuid(), $this->faker->email()),
            now()->addSeconds(1),
            $this->faker->uuid(),
            now(),
        );

        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use ($mockedRefreshToken) {
                $mock->shouldReceive('find')
                    ->once()
                    ->with($mockedRefreshToken->id)
                    ->andReturn($mockedRefreshToken);
            }
        );
        assert($mockCacher instanceof Cacher);


        // Assert
        $mockException = new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token is already used before.',
            RefreshTokenExceptionCode::TOKEN_IS_USED->value,
        ));
        $this->expectExceptionObject($mockException);


        // Act
        $this->makeService(null, $mockCacher)->invalidate($mockedID);
    }

    #[Test]
    public function should_successfully_invalidate_old_refresh_token(): void
    {
        // Arrange
        $mockedID = $this->faker->uuid();
        $mockedRefreshToken = new RefreshTokenClaims(
            $mockedID,
            new RefreshTokenClaimsUser($this->faker->uuid(), $this->faker->email()),
            now()->addSeconds(1),
            $this->faker->uuid(),
        );

        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use ($mockedRefreshToken) {
                $mock->shouldReceive('find')
                    ->once()
                    ->with($mockedRefreshToken->id)
                    ->andReturn($mockedRefreshToken);

                $oldRefreshTokenAfterMarkAsUsed = clone $mockedRefreshToken;
                $oldRefreshTokenAfterMarkAsUsed->usedAt = now();

                $mock->shouldReceive('save')
                    ->once()
                    ->withArgs(function (
                        RefreshTokenClaims $argRefreshToken,
                    ) use ($oldRefreshTokenAfterMarkAsUsed) {
                        try {
                            $this->assertSame(
                                $oldRefreshTokenAfterMarkAsUsed->id,
                                $argRefreshToken->id,
                            );
                            $this->assertEquals(
                                $oldRefreshTokenAfterMarkAsUsed->user->id,
                                $argRefreshToken->user->id,
                            );
                            $this->assertEquals(
                                $oldRefreshTokenAfterMarkAsUsed->user->userEmail,
                                $argRefreshToken->user->userEmail,
                            );
                            $this->assertEquals(
                                $oldRefreshTokenAfterMarkAsUsed->usedAt,
                                $argRefreshToken->usedAt,
                            );
                            $this->assertEquals(
                                $oldRefreshTokenAfterMarkAsUsed->expiredAt,
                                $argRefreshToken->expiredAt,
                            );
                            $this->assertSame(
                                $oldRefreshTokenAfterMarkAsUsed->childID,
                                $argRefreshToken->childID,
                            );

                            return true;
                        } catch (\Throwable $e) {
                            dump($e);
                            return false;
                        }
                    })->andReturnNull();
            }
        );
        assert($mockCacher instanceof Cacher);


        // Act
        $this->makeService(null, $mockCacher)->invalidate($mockedID);
    }
}
